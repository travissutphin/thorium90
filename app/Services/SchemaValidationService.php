<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SchemaValidationService
{
    /**
     * Get available schema types.
     */
    public function getAvailableTypes(): array
    {
        $types = Config::get('schema.types', []);
        
        return collect($types)->map(function ($config, $type) {
            return [
                'value' => $type,
                'label' => $config['label'] ?? $type,
                'description' => $config['description'] ?? null,
            ];
        })->values()->toArray(); // Add ->values() to reindex to numeric array
    }

    /**
     * Get schema type configuration.
     */
    public function getTypeConfig(string $type): ?array
    {
        return Config::get("schema.types.{$type}");
    }

    /**
     * Validate schema data based on type.
     */
    public function validateSchemaData(string $type, array $data): array
    {
        $config = $this->getTypeConfig($type);
        
        if (!$config) {
            throw new ValidationException(
                Validator::make([], ['schema_type' => 'required'], [
                    'schema_type.required' => "Unknown schema type: {$type}"
                ])
            );
        }

        // Get validation rules for this type
        $rules = $this->buildValidationRules($type, $config);
        
        // Validate the data
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Build validation rules for a schema type.
     */
    protected function buildValidationRules(string $type, array $config): array
    {
        $rules = [];
        
        // Add base rules from global config (but don't require schema_type since it's passed as parameter)
        $globalRules = Config::get('schema.global_validation', []);
        unset($globalRules['schema_type']); // Remove since it's validated externally
        $rules = array_merge($rules, $globalRules);
        
        // Add type-specific field rules
        if (isset($config['fields'])) {
            foreach ($config['fields'] as $field => $rule) {
                $rules["schema_data.{$field}"] = $rule;
            }
        }
        
        // If this type extends another, inherit its rules
        if (isset($config['extends'])) {
            $parentConfig = $this->getTypeConfig($config['extends']);
            if ($parentConfig && isset($parentConfig['fields'])) {
                foreach ($parentConfig['fields'] as $field => $rule) {
                    // Don't override if already defined
                    if (!isset($rules["schema_data.{$field}"])) {
                        $rules["schema_data.{$field}"] = $rule;
                    }
                }
            }
        }
        
        return $rules;
    }

    /**
     * Generate default schema data for a type.
     */
    public function generateDefaultSchemaData(string $type, array $pageData = []): array
    {
        $config = $this->getTypeConfig($type);
        
        if (!$config) {
            return [];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $type,
        ];

        // Auto-generate properties based on configuration
        $autoGenerate = Config::get('schema.auto_generate', []);
        
        foreach ($autoGenerate as $schemaProperty => $source) {
            $value = $this->generatePropertyValue($source, $pageData);
            if ($value !== null) {
                $schema[$schemaProperty] = $value;
            }
        }

        // Add required properties if not already present
        if (isset($config['required_properties'])) {
            foreach ($config['required_properties'] as $property) {
                if (!isset($schema[$property])) {
                    $schema[$property] = $this->getDefaultValueForProperty($property, $pageData);
                }
            }
        }

        return $schema;
    }

    /**
     * Generate a property value based on source configuration.
     */
    protected function generatePropertyValue(string $source, array $pageData)
    {
        switch ($source) {
            case 'computed':
                return null; // Will be computed later
                
            case 'title':
                return $pageData['title'] ?? null;
                
            case 'excerpt':
                return $pageData['excerpt'] ?? $pageData['meta_description'] ?? null;
                
            case 'app.locale':
                return Config::get('app.locale', 'en');
                
            default:
                // Check if it's a direct page data key
                return $pageData[$source] ?? null;
        }
    }

    /**
     * Get default value for a property.
     */
    protected function getDefaultValueForProperty(string $property, array $pageData)
    {
        switch ($property) {
            case 'name':
                return $pageData['title'] ?? '';
                
            case 'description':
                return $pageData['excerpt'] ?? $pageData['meta_description'] ?? '';
                
            case 'headline':
                return $pageData['title'] ?? '';
                
            case 'articleBody':
                return strip_tags($pageData['content'] ?? '');
                
            default:
                return null;
        }
    }

    /**
     * Merge user-provided schema data with defaults.
     */
    public function mergeWithDefaults(string $type, array $userSchemaData, array $pageData = []): array
    {
        $defaults = $this->generateDefaultSchemaData($type, $pageData);
        
        // Merge user data with defaults, user data takes precedence
        $merged = array_merge($defaults, $userSchemaData);
        
        // Ensure @type is always set correctly
        $merged['@type'] = $type;
        $merged['@context'] = 'https://schema.org';
        
        return $merged;
    }

    /**
     * Get schema validation rules for request validation.
     */
    public function getValidationRulesForRequest(string $type): array
    {
        $config = $this->getTypeConfig($type);
        
        if (!$config) {
            return [];
        }

        return $this->buildValidationRules($type, $config);
    }
}