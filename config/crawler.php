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
            ],
            'rate_limit' => 2,
        ],
        'Associated Press' => [
            'category_selectors' => [
                 'links' => '.Link', // CSS Selector برای لینک‌های اخبار در صفحه دسته‌بندی
                'filter'=>'article'
            ],
            'news_selectors' => [
               'title' => 'h1.Page-headline',
                'content' => '.RichTextStoryBody',
              'cover' => '.Page-lead .Carousel-slide img.Image',
                'cover_alt' => 'meta[property="og:image"]',
            ],
            'rate_limit' => 2,
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
            ],
            'rate_limit' => 2,
        ],

    ],
];
