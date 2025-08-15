<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Core\Plugin\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PluginController extends Controller
{
    protected PluginManager $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * Display a listing of plugins
     */
    public function index(Request $request)
    {
        $this->authorize('manage plugins');

        // Discover plugins
        $this->pluginManager->discoverPlugins();

        $query = $this->pluginManager->getAllPlugins();

        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query = $this->pluginManager->getActivePlugins();
            } elseif ($status === 'inactive') {
                $allPlugins = $this->pluginManager->getAllPlugins();
                $activePlugins = $this->pluginManager->getActivePlugins();
                $query = $allPlugins->reject(function ($plugin) use ($activePlugins) {
                    return $activePlugins->has($plugin->getId());
                });
            }
        }

        // Filter by category
        if ($request->has('category') && $request->get('category') !== 'all') {
            $category = $request->get('category');
            $query = $query->filter(function ($plugin) use ($category) {
                return $plugin->getCategory() === $category;
            });
        }

        // Search
        if ($request->has('search') && !empty($request->get('search'))) {
            $search = strtolower($request->get('search'));
            $query = $query->filter(function ($plugin) use ($search) {
                return str_contains(strtolower($plugin->getName()), $search) ||
                       str_contains(strtolower($plugin->getDescription()), $search) ||
                       str_contains(strtolower($plugin->getAuthor()), $search);
            });
        }

        $plugins = $query->map(function ($plugin) {
            return [
                'id' => $plugin->getId(),
                'name' => $plugin->getName(),
                'version' => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'author' => $plugin->getAuthor(),
                'category' => $plugin->getCategory(),
                'is_active' => $this->pluginManager->isPluginEnabled($plugin->getId()),
                'manifest' => $plugin->getManifest(),
            ];
        })->values();

        $stats = $this->pluginManager->getStats();

        // Get available categories
        $categories = $this->pluginManager->getAllPlugins()
            ->map(fn($plugin) => $plugin->getCategory())
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('admin/plugins/index', [
            'plugins' => $plugins,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => $request->only(['status', 'category', 'search']),
        ]);
    }

    /**
     * Show plugin details
     */
    public function show(string $pluginId)
    {
        $this->authorize('manage plugins');

        $plugin = $this->pluginManager->getPlugin($pluginId);

        if (!$plugin) {
            return redirect()->route('admin.plugins.index')
                           ->with('error', 'Plugin not found.');
        }

        return Inertia::render('admin/plugins/show', [
            'plugin' => [
                'id' => $plugin->getId(),
                'name' => $plugin->getName(),
                'version' => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'author' => $plugin->getAuthor(),
                'category' => $plugin->getCategory(),
                'is_active' => $this->pluginManager->isPluginEnabled($plugin->getId()),
                'manifest' => $plugin->getManifest(),
                'templates' => $plugin->getTemplates(),
                'layouts' => $plugin->getLayouts(),
                'blocks' => $plugin->getBlocks(),
                'themes' => $plugin->getThemes(),
            ],
        ]);
    }

    /**
     * Enable a plugin
     */
    public function enable(string $pluginId)
    {
        $this->authorize('manage plugins');

        $success = $this->pluginManager->enablePlugin($pluginId);

        if ($success) {
            return back()->with('success', 'Plugin enabled successfully.');
        }

        return back()->with('error', 'Failed to enable plugin.');
    }

    /**
     * Disable a plugin
     */
    public function disable(string $pluginId)
    {
        $this->authorize('manage plugins');

        $success = $this->pluginManager->disablePlugin($pluginId);

        if ($success) {
            return back()->with('success', 'Plugin disabled successfully.');
        }

        return back()->with('error', 'Failed to disable plugin.');
    }

    /**
     * Install a plugin from uploaded file
     */
    public function install(Request $request)
    {
        $this->authorize('manage plugins');

        $request->validate([
            'plugin_file' => 'required|file|mimes:zip|max:10240', // 10MB max
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('plugin_file');
        
        // Store the uploaded file temporarily
        $tempPath = $file->store('temp/plugins');
        $fullPath = Storage::path($tempPath);

        try {
            $success = $this->pluginManager->installPlugin($fullPath);

            if ($success) {
                return redirect()->route('admin.plugins.index')
                               ->with('success', 'Plugin installed successfully.');
            }

            return back()->with('error', 'Failed to install plugin.');

        } finally {
            // Clean up temporary file
            Storage::delete($tempPath);
        }
    }

    /**
     * Uninstall a plugin
     */
    public function uninstall(string $pluginId)
    {
        $this->authorize('manage plugins');

        $plugin = $this->pluginManager->getPlugin($pluginId);

        if (!$plugin) {
            return back()->with('error', 'Plugin not found.');
        }

        $success = $this->pluginManager->uninstallPlugin($pluginId);

        if ($success) {
            return redirect()->route('admin.plugins.index')
                           ->with('success', 'Plugin uninstalled successfully.');
        }

        return back()->with('error', 'Failed to uninstall plugin.');
    }

    /**
     * Bulk actions for plugins
     */
    public function bulkAction(Request $request)
    {
        $this->authorize('manage plugins');

        $request->validate([
            'action' => 'required|in:enable,disable',
            'plugin_ids' => 'required|array',
            'plugin_ids.*' => 'string',
        ]);

        $action = $request->get('action');
        $pluginIds = $request->get('plugin_ids');
        $results = [];

        foreach ($pluginIds as $pluginId) {
            if ($action === 'enable') {
                $results[$pluginId] = $this->pluginManager->enablePlugin($pluginId);
            } else {
                $results[$pluginId] = $this->pluginManager->disablePlugin($pluginId);
            }
        }

        $successCount = count(array_filter($results));
        $totalCount = count($pluginIds);

        if ($successCount === $totalCount) {
            $message = ucfirst($action) . "d {$successCount} plugins successfully.";
            return back()->with('success', $message);
        } elseif ($successCount > 0) {
            $message = ucfirst($action) . "d {$successCount} of {$totalCount} plugins.";
            return back()->with('warning', $message);
        } else {
            $message = "Failed to {$action} any plugins.";
            return back()->with('error', $message);
        }
    }

    /**
     * Clear plugin cache
     */
    public function clearCache()
    {
        $this->authorize('manage plugins');

        $this->pluginManager->clearCache();

        return back()->with('success', 'Plugin cache cleared successfully.');
    }

    /**
     * Get plugin statistics for API
     */
    public function stats()
    {
        $this->authorize('manage plugins');

        return response()->json($this->pluginManager->getStats());
    }

    /**
     * Export plugin list
     */
    public function export()
    {
        $this->authorize('manage plugins');

        $plugins = $this->pluginManager->getAllPlugins()->map(function ($plugin) {
            return [
                'id' => $plugin->getId(),
                'name' => $plugin->getName(),
                'version' => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'author' => $plugin->getAuthor(),
                'category' => $plugin->getCategory(),
                'is_active' => $this->pluginManager->isPluginEnabled($plugin->getId()),
            ];
        });

        $filename = 'plugins_' . date('Y-m-d_H-i-s') . '.json';

        return response()->json($plugins)
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
