<?php

return [
    'sites' => [
        'The New York Times' => [
            'category_selectors' => [
                'links' => 'a.css-1u3p7j1, a.css-8hzhxf, a[href*="/202"]',
                'filter' => '',
            ],
            'news_selectors' => [
                'title' => 'h1[data-testid="headline"]',
                'content' => 'section[name="articleBody"]',
                'cover' => 'figure[itemprop="image"] img',
                'cover_carousel' => '[data-testid="slideshow-container"] figure:first-child img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => [
                    'aside', '.ad', '.inline-ad', '.banner',
                    '[data-testid*="ad-"]', '[data-testid="related-content"]',
                    '[data-testid="bottom-of-article"]', '[data-testid="newsletter-promo"]',
                    '.css-158dogj', 'div[role="toolbar"]'
                ],
            ],
            'rate_limit' => 2,
        ],
        'Associated Press' => [
            'category_selectors' => [
                'links' => 'a[href*="/article/"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.RichTextStoryBody, .StoryBody, main article',
                'cover' => 'meta[property="og:image"]',
                'cover_carousel' => 'figure img',
                'cover_alt' => 'meta[property="twitter:image"]',
                'unwanted_content_selectors' => [
                    '.ad', '.Enhancement', '.RelatedList', 'aside', 'script', '.AP-catchup-module'
                ],
            ],
            'rate_limit' => 3,
        ],
        'Al Jazeera' => [
            'category_selectors' => [
                'links' => 'a[href*="/news/"], a.u-clickable-card__link',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.wysiwyg--all-content, .article-body',
                'cover' => '.responsive-image img',
                'cover_carousel' => 'figure img:first-child',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => [
                    '.ad', '.ads-container', '.banner', '.sponsored-content', '.share-social-media',
                    '.related-content-card', '.related-articles', '.article-more-stories',
                    '.recommended-stories', 'iframe', 'script', 'nav', 'footer', '.comments-section', 'aside',
                ],
            ],
            'rate_limit' => 2,
        ],
        'Bloomberg' => [
            'category_selectors' => [
                'links' => 'a[href*="/news/articles/"], .u-clickable-card__link',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.BasicContent_shareSmallDesktop__yhFgh, .body-copy, .article-body',
                'cover' => 'meta[property="og:image"]',
                'cover_carousel' => '.CarouselSlide-media:first-child img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => [
                    '.ad', '.advertisement', '.inline-content', '.PageListEnhancementGeneric'
                ],
            ],
            'rate_limit' => 3,
        ],
        'Guardian' => [
            'category_selectors' => [
                'links' => '.fc-item__link, a[data-link-name="article"]',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => 'div#content-body, div[data-gu-name="article-body"]',
                'cover' => 'figure img',
                'cover_carousel' => 'figure.gallery .gallery-item:first-child img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.sponsor', '.inline-content'],
            ],
            'rate_limit' => 2,
        ],
        'The Wall Street Journal' => [
            'category_selectors' => [
                'links' => '.Link, a[href*="/articles/"]',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1.Page-headline',
                'content' => '.RichTextStoryBody, article',
                'cover' => 'meta[property="og:image"]',
                'cover_carousel' => '.CarouselSlide-media:first-child img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.advertisement', '.inline-content'],
            ],
            'rate_limit' => 3,
        ],
        'Financial Times' => [
            'category_selectors' => [
                'links' => '.js-teaser-standfirst-link, a[href*="/content/"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => 'div[data-testid="article-body"]',
                'cover' => 'figure img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.n-myft-ui', '.share-buttons', '.related-articles'],
            ],
            'rate_limit' => 2,
        ],
        'BBC News' => [
            'category_selectors' => [
                'links' => 'a.gs-c-promo-heading, a[data-testid="internal-link"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => 'article div[data-component="text-block"], .ssrcss-uf6wea-RichTextComponent',
                'cover' => 'meta[property="og:image"]',
                'cover_carousel' => 'figure img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', 'nav', 'footer', 'aside'],
            ],
            'rate_limit' => 2,
        ],
        'Reuters' => [
            'category_selectors' => [
                'links' => 'a[data-testid="Heading"], a[href*="/article/"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => 'div[data-testid="ArticleBody"], .article-body__content',
                'cover' => 'meta[property="og:image"]',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.article-controls', '.related-content'],
            ],
            'rate_limit' => 2,
        ],
        'CNN' => [
            'category_selectors' => [
                'links' => 'a.container__link, a[href*="/202"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1.headline__text',
                'content' => 'div.article__content, .article__main',
                'cover' => 'meta[property="og:image"]',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.article__aside', '.article__related'],
            ],
            'rate_limit' => 2,
        ],
        'TechCrunch' => [
            'category_selectors' => [
                'links' => '.loop-card__title-link, a[href*="/202"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.article-content, .entry-content',
                'cover' => 'meta[property="og:image"]',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.article-sidebar', '.embed-container'],
            ],
            'rate_limit' => 2,
        ],
        'The Verge' => [
            'category_selectors' => [
                'links' => 'a[href*="/202"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.duet--article--article-body-component',
                'cover' => 'meta[property="og:image"]',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', 'aside', '.duet--article--sidebar'],
            ],
            'rate_limit' => 2,
        ],
        'CNBC' => [
            'category_selectors' => [
                'links' => 'a.Card-title, a[href*="/202"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.ArticleBody-articleBody',
                'cover' => 'meta[property="og:image"]',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.ArticleAside-aside', '.RelatedContent-container'],
            ],
            'rate_limit' => 2,
        ],
        'VentureBeat' => [
            'category_selectors' => [
                'links' => 'a.article-title, a[href*="/202"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1.article-title',
                'content' => '.article-content',
                'cover' => 'meta[property="og:image"]',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', 'aside'],
            ],
            'rate_limit' => 2,
        ],
        'Wired' => [
            'category_selectors' => [
                'links' => 'a.SummaryItemHedLink-civKyv, a[href*="/story/"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.body__inner-container',
                'cover' => 'meta[property="og:image"]',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', 'aside', '.newsletter-signup-inline'],
            ],
            'rate_limit' => 2,
        ],
        'Economist' => [
            'category_selectors' => [
                'links' => 'a[href*="/202"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.meteredContent',
                'cover' => 'meta[property="og:image"]',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.inline-ad', '.promo'],
            ],
            'rate_limit' => 2,
        ],
        'Sky News' => [
            'category_selectors' => [
                'links' => '.sdc-site-tile__headline-link, a[href*="/story/"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.sdc-article-body',
                'cover' => 'meta[property="og:image"]',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.sdc-article-aside'],
            ],
            'rate_limit' => 2,
        ],
        'National Geographic' => [
            'category_selectors' => [
                'links' => 'a[href*="/article/"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.article-body',
                'cover' => 'meta[property="og:image"]',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', 'aside'],
            ],
            'rate_limit' => 2,
        ],
    ],
];
