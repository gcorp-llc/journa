<?php

return [
    'sites' => [
        'The New York Times' => [
            'category_selectors' => [
                'links' => 'a.css-1u3p7j1',
                'filter' => '/\/2025\/[0-1][0-9]\/[0-3][0-9]\/world\//',
            ],
            'news_selectors' => [
                'title' => 'h1.css-v3oks',
                'content' => '.StoryBodyCompanionColumn .css-53u6y8',
                'cover' => 'img.css-1ibr5m9',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.inline-ad', '.banner'],
            ],
            'rate_limit' => 2,
        ],
        'Guardian' => [
            'category_selectors' => [
                'links' => '.fc-item__link',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => 'div#content-body',
                'cover' => 'figure img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.sponsor', '.inline-content'],
            ],
            'rate_limit' => 2,
        ],
        'Al Jazeera' => [
            'category_selectors' => [
                'links' => '.u-clickable-card__link',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.wysiwyg--all-content',
                'cover' => '.article-featured-image img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.banner', '.sponsored-content'],
            ],
            'rate_limit' => 2,
        ],
        'Associated Press' => [
            'category_selectors' => [
                'links' => '.Link',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1.Page-headline',
                'content' => '.RichTextStoryBody',
                'cover' => '.CarouselSlide-media picture source[media*="min-width: 1024px"][type="image/webp"], .CarouselSlide-media img.Image',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => [
                    '.inline-content',
                    '.ad',
                    '.advertisement',
                    '.Component-richTextAd-0',
                    '.Component-video-0',
                    '.Component-image-0',
                    '.Component-caption-0',
                    '.PageListEnhancementGeneric',
                    '.Advertisement'
                ],
            ],
            'rate_limit' => 5,
        ],
        'Financial Times' => [
            'category_selectors' => [
                'links' => '.fc-item__link',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => 'div#content-body',
                'cover' => 'figure img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.sponsor', '.inline-ad'],
            ],
            'rate_limit' => 2,
        ],
        'bloomberg' => [
            'category_selectors' => [
                'links' => '.u-clickable-card__link',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.wysiwyg--all-content',
                'cover' => '.article-featured-image img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.banner', '.sponsored-content'],
            ],
            'rate_limit' => 2,
        ],
        'economist' => [
            'category_selectors' => [
                'links' => '.css-4svvz1 .eb97p610',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'a.css-1u3p7j1',
                'content' => '.meteredContent',
                'cover' => 'img.css-119ags5',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.inline-ad', '.promo'],
            ],
            'rate_limit' => 2,
        ],
        'sky news' => [
            'category_selectors' => [
                'links' => '.fc-item__link',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => 'div#content-body',
                'cover' => 'figure img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.banner', '.sponsor'],
            ],
            'rate_limit' => 2,
        ],
        'nationalgeographic' => [
            'category_selectors' => [
                'links' => '.u-clickable-card__link',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.wysiwyg--all-content',
                'cover' => '.article-featured-image img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.promo', '.sponsored-content'],
            ],
            'rate_limit' => 2,
        ],
        'wired' => [
            'category_selectors' => [
                'links' => '.css-4svvz1 .eb97p610',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'a.css-1u3p7j1',
                'content' => '.meteredContent',
                'cover' => 'img.css-119ags5',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.inline-ad', '.promo'],
            ],
            'rate_limit' => 2,
        ],
        'techcrunch' => [
            'category_selectors' => [
                'links' => '.fc-item__link',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => 'div#content-body',
                'cover' => 'figure img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.banner', '.sponsor'],
            ],
            'rate_limit' => 2,
        ],
    ],
];
