<?php

namespace App\Features\Blog\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FAQDetector
{
    /**
     * Question patterns to detect potential FAQ items.
     */
    protected array $questionPatterns = [
        'what' => '/\b(what is|what are|what does|what do|what can|what should|what will)\b/i',
        'how' => '/\b(how to|how do|how does|how can|how should|how will|how long|how much)\b/i',
        'why' => '/\b(why do|why does|why should|why would|why is|why are)\b/i',
        'when' => '/\b(when to|when do|when does|when should|when would|when is)\b/i',
        'where' => '/\b(where to|where do|where does|where should|where can|where is)\b/i',
        'which' => '/\b(which is|which are|which do|which does|which should|which can)\b/i',
        'can' => '/\b(can you|can i|can we|can it)\b/i',
        'is' => '/\b(is it|is there|is this)\b/i',
        'are' => '/\b(are there|are these|are they)\b/i',
    ];

    /**
     * Detect FAQ items from content structure and patterns.
     */
    public function detectFAQs(string $content): Collection
    {
        $faqs = collect();

        // Strategy 1: Extract from explicit Q&A patterns
        $explicitFAQs = $this->extractExplicitFAQs($content);
        $faqs = $faqs->merge($explicitFAQs);

        // Strategy 2: Extract from headings that look like questions
        $headingFAQs = $this->extractQuestionHeadings($content);
        $faqs = $faqs->merge($headingFAQs);

        // Strategy 3: Extract from bold question patterns
        $boldFAQs = $this->extractBoldQuestions($content);
        $faqs = $faqs->merge($boldFAQs);

        // Strategy 4: Generate common questions based on content
        $generatedFAQs = $this->generateCommonQuestions($content);
        $faqs = $faqs->merge($generatedFAQs);

        return $faqs->unique('question')->take(5);
    }

    /**
     * Extract FAQ items from explicit Q&A patterns in content.
     */
    protected function extractExplicitFAQs(string $content): Collection
    {
        $faqs = collect();

        // Pattern: Q: question A: answer
        if (preg_match_all('/Q:\s*(.+?)\s*A:\s*(.+?)(?=Q:|$)/si', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $faqs->push([
                    'id' => Str::uuid(),
                    'question' => trim(strip_tags($match[1])),
                    'answer' => trim(strip_tags($match[2])),
                    'confidence' => 95,
                    'source' => 'explicit_qa'
                ]);
            }
        }

        // Pattern: Question: answer
        if (preg_match_all('/Question:\s*(.+?)\s*(?:Answer:|$)(.+?)(?=Question:|$)/si', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (count($match) >= 3) {
                    $faqs->push([
                        'id' => Str::uuid(),
                        'question' => trim(strip_tags($match[1])),
                        'answer' => trim(strip_tags($match[2])),
                        'confidence' => 90,
                        'source' => 'explicit_question'
                    ]);
                }
            }
        }

        return $faqs;
    }

    /**
     * Extract questions from headings (H1-H6).
     */
    protected function extractQuestionHeadings(string $content): Collection
    {
        $faqs = collect();

        // Extract all headings
        if (preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $content, $headingMatches, PREG_SET_ORDER)) {
            foreach ($headingMatches as $headingMatch) {
                $headingText = strip_tags($headingMatch[2]);
                
                // Check if heading looks like a question
                if ($this->isQuestion($headingText)) {
                    // Try to find the answer in the following content
                    $answer = $this->findAnswerAfterHeading($content, $headingMatch[0]);
                    
                    if ($answer) {
                        $faqs->push([
                            'id' => Str::uuid(),
                            'question' => trim($headingText),
                            'answer' => $answer,
                            'confidence' => 80,
                            'source' => 'heading_question'
                        ]);
                    }
                }
            }
        }

        return $faqs;
    }

    /**
     * Extract questions from bold text patterns.
     */
    protected function extractBoldQuestions(string $content): Collection
    {
        $faqs = collect();

        // Extract bold text that might be questions
        if (preg_match_all('/<(?:b|strong)>(.*?)<\/(?:b|strong)>/i', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $boldText = strip_tags($match[1]);
                
                if ($this->isQuestion($boldText) && strlen($boldText) > 10) {
                    // Try to find answer in following text
                    $answer = $this->findAnswerAfterElement($content, $match[0]);
                    
                    if ($answer) {
                        $faqs->push([
                            'id' => Str::uuid(),
                            'question' => trim($boldText),
                            'answer' => $answer,
                            'confidence' => 70,
                            'source' => 'bold_question'
                        ]);
                    }
                }
            }
        }

        return $faqs;
    }

    /**
     * Generate common questions based on content analysis.
     */
    protected function generateCommonQuestions(string $content): Collection
    {
        $faqs = collect();
        $contentLower = strtolower(strip_tags($content));

        // Common question templates based on content patterns
        $questionTemplates = [
            'installation' => [
                'question' => 'How do I install this?',
                'keywords' => ['install', 'installation', 'setup', 'composer', 'npm'],
            ],
            'configuration' => [
                'question' => 'How do I configure this?',
                'keywords' => ['config', 'configuration', 'settings', 'environment'],
            ],
            'troubleshooting' => [
                'question' => 'What are common issues and solutions?',
                'keywords' => ['error', 'problem', 'issue', 'troubleshoot', 'fix'],
            ],
            'best_practices' => [
                'question' => 'What are the best practices?',
                'keywords' => ['best practice', 'recommend', 'should', 'avoid'],
            ],
            'performance' => [
                'question' => 'How can I improve performance?',
                'keywords' => ['performance', 'optimization', 'speed', 'cache'],
            ],
            'security' => [
                'question' => 'How do I secure this implementation?',
                'keywords' => ['security', 'secure', 'protection', 'vulnerability'],
            ],
        ];

        foreach ($questionTemplates as $category => $template) {
            $keywordCount = 0;
            foreach ($template['keywords'] as $keyword) {
                if (stripos($contentLower, $keyword) !== false) {
                    $keywordCount++;
                }
            }

            if ($keywordCount >= 2) {
                $answer = $this->generateAnswerFromContent($content, $template['keywords']);
                if ($answer) {
                    $faqs->push([
                        'id' => Str::uuid(),
                        'question' => $template['question'],
                        'answer' => $answer,
                        'confidence' => 50 + ($keywordCount * 10),
                        'source' => 'generated_' . $category
                    ]);
                }
            }
        }

        return $faqs;
    }

    /**
     * Check if a text string looks like a question.
     */
    protected function isQuestion(string $text): bool
    {
        $text = trim($text);

        // Ends with question mark
        if (substr($text, -1) === '?') {
            return true;
        }

        // Starts with question word
        foreach ($this->questionPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find answer text following a heading.
     */
    protected function findAnswerAfterHeading(string $content, string $heading): ?string
    {
        $headingPos = strpos($content, $heading);
        if ($headingPos === false) {
            return null;
        }

        $afterHeading = substr($content, $headingPos + strlen($heading));
        
        // Extract content until next heading
        if (preg_match('/^(.*?)(?=<h[1-6]|$)/si', $afterHeading, $match)) {
            $answerText = strip_tags($match[1]);
            $answerText = trim(preg_replace('/\s+/', ' ', $answerText));
            
            if (strlen($answerText) > 20 && strlen($answerText) < 500) {
                return $answerText;
            }
        }

        return null;
    }

    /**
     * Find answer text following an element.
     */
    protected function findAnswerAfterElement(string $content, string $element): ?string
    {
        $elementPos = strpos($content, $element);
        if ($elementPos === false) {
            return null;
        }

        $afterElement = substr($content, $elementPos + strlen($element));
        
        // Extract next paragraph or sentence
        if (preg_match('/^\s*([^<]*?)(?=<|\.|$)/s', $afterElement, $match)) {
            $answerText = trim($match[1]);
            
            if (strlen($answerText) > 15 && strlen($answerText) < 300) {
                return $answerText;
            }
        }

        return null;
    }

    /**
     * Generate answer from content based on keywords.
     */
    protected function generateAnswerFromContent(string $content, array $keywords): ?string
    {
        $sentences = $this->extractSentences($content);
        $relevantSentences = collect();

        foreach ($sentences as $sentence) {
            $relevanceScore = 0;
            foreach ($keywords as $keyword) {
                if (stripos($sentence, $keyword) !== false) {
                    $relevanceScore++;
                }
            }

            if ($relevanceScore >= 1) {
                $relevantSentences->push([
                    'text' => $sentence,
                    'score' => $relevanceScore
                ]);
            }
        }

        if ($relevantSentences->isEmpty()) {
            return null;
        }

        // Get the most relevant sentences
        $topSentences = $relevantSentences
            ->sortByDesc('score')
            ->take(2)
            ->pluck('text')
            ->implode(' ');

        return strlen($topSentences) > 20 ? $topSentences : null;
    }

    /**
     * Extract sentences from content.
     */
    protected function extractSentences(string $content): array
    {
        $cleanContent = strip_tags($content);
        $cleanContent = preg_replace('/\s+/', ' ', $cleanContent);
        
        // Split by periods, question marks, and exclamation marks
        $sentences = preg_split('/[.!?]+/', $cleanContent);
        
        return array_filter(array_map('trim', $sentences), function ($sentence) {
            return strlen($sentence) > 15 && strlen($sentence) < 500;
        });
    }
}