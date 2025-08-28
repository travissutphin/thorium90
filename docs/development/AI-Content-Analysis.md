# AI Content Analysis System

## Overview
The AI Content Analysis system provides intelligent suggestions for blog content optimization including:
- **Tag Suggestions**: AI-powered tag recommendations based on content analysis
- **Keyword Extraction**: SEO-friendly keyword identification using TF-IDF-like algorithms
- **Topic Classification**: Automatic content categorization (tutorial, review, guide, etc.)
- **FAQ Detection**: Identifies potential FAQ content from blog posts

## Architecture

### Backend Services
Located in `app/Features/Blog/Services/`:

1. **BlogContentAnalyzer** - Main orchestrator service
2. **KeywordExtractor** - NLP-based keyword extraction
3. **TagSuggestionEngine** - Multi-strategy tag suggestions
4. **FAQDetector** - FAQ pattern detection
5. **ContentClassifier** - Content type classification

### API Endpoints
- `POST /admin/blog/analysis/content` - Analyze title/content for suggestions
- `GET /admin/blog/analysis/tags/suggest` - Get tag suggestions
- `POST /admin/blog/analysis/faqs/generate` - Generate FAQ suggestions
- `GET /admin/blog/analysis/stats` - Get analysis statistics

### Frontend Component
- **ContentAnalysisPanel** (`resources/js/components/aeo/ContentAnalysisPanel.tsx`)
- Integrated into blog Create/Edit forms
- Real-time analysis with 2-second debounce
- Tabbed interface for different suggestion types

## Configuration

### Blog Configuration (`config/blog.php`)
```php
'ai' => [
    'tech_terms' => [...], // Technology terms for detection
    'analysis' => [
        'max_keywords' => 10,
        'max_tags' => 8,
        'max_topics' => 5,
        'max_faqs' => 5,
        'min_confidence' => 50,
    ],
    'faq' => [
        'min_question_length' => 10,
        'confidence_threshold' => 60,
    ],
    'tags' => [
        'similarity_threshold' => 0.6,
        'popularity_boost' => 20,
    ]
]
```

## Usage

### User Workflow
1. User enters blog title and content
2. ContentAnalysisPanel automatically analyzes after 2-second delay
3. AI suggestions appear in tabbed interface:
   - **Tags**: Clickable badges with confidence scores
   - **Keywords**: SEO-friendly terms for meta keywords
   - **Topics**: Content categorization
   - **FAQs**: Extracted Q&A pairs
4. User selects desired suggestions
5. Form fields are automatically populated

### Integration with Existing Components
- **Tags**: Auto-populates tag selection sidebar
- **Keywords**: Updates meta_keywords field and AEO KeywordManager
- **Topics**: Updates AEO TopicSelector
- **FAQs**: Adds to AEO FAQEditor
- **Content Type**: Auto-updates schema type dropdown

## Analysis Algorithms

### Keyword Extraction
- TF-IDF-like frequency analysis
- Stop word filtering
- Technology term detection
- Phrase extraction for multi-word keywords

### Tag Suggestions
1. **Direct Matching**: Keywords → existing tags
2. **Technology Detection**: Technical terms from config
3. **Content Classification**: Based on content type
4. **Skill Level**: Beginner, intermediate, advanced detection
5. **Popular Tags**: Trending tags from database

### FAQ Detection
1. **Explicit Patterns**: "Q:", "A:" format detection
2. **Question Headers**: H2/H3 tags with question words
3. **Bold Questions**: Bold text followed by answers
4. **Common Questions**: Generated based on content themes

### Content Classification
Uses pattern matching and structural analysis:
- **Tutorial**: Step-by-step patterns, "how to" phrases
- **Review**: Comparison terms, pros/cons
- **News**: Recent dates, announcement terminology  
- **Guide**: Comprehensive, reference materials
- **Analysis**: Research terms, data points
- **Blog Post**: Personal pronouns, opinions (default)

## Performance Considerations
- Analysis results cached for improved performance
- Debounced API calls prevent excessive requests
- Lightweight algorithms suitable for real-time analysis
- Database queries optimized with proper indexing

## Future Enhancements
- External AI API integration (OpenAI, Claude)
- Machine learning model training
- User feedback loop for suggestion improvement
- Bulk analysis for existing posts
- Advanced sentiment analysis

## Testing
```bash
# Test AI analysis services
php artisan test --filter="ContentAnalysis"

# Test API endpoints
php artisan test --filter="BlogAnalysis"
```

## Monitoring
- Analysis statistics cached and available via API
- Success/failure rates tracked
- Popular suggested tags monitored
- Content type distribution analyzed