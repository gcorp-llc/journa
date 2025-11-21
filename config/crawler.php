<?php

return [
    'sites' => [
        'The New York Times' => [
            'category_selectors' => [
                // سلکتورهای شما برای لینک‌ها درست به نظر می‌رسند
                'links' => 'a.css-1u3p7j1, a.css-8hzhxf',
                // توجه: فیلتر خالی لینک‌های ویدئو را هم عبور می‌دهد
                'filter' => '',
            ],
            'news_selectors' => [
                // استفاده از data-testid به جای کلاس css شکننده
                'title' => 'h1[data-testid="headline"]',

                // استفاده از تگ section معنایی برای بدنه اصلی مقاله
                'content' => 'section[name="articleBody"]',

                // انتخاب تصویر اصلی مقاله (برای مقالات تک‌عکسی) با استفاده از itemprop
                'cover' => 'figure[itemprop="image"] img',

                // انتخاب اولین تصویر از اسلایدر (اگر مقاله اسلایدر داشته باشد)
                // کد ProcessNewsImageJob ابتدا این را چک می‌کند
                'cover_carousel' => '[data-testid="slideshow-container"] figure:first-child img',

                // فال‌بک اصلی شما همچنان پابرجاست
                'cover_alt' => 'meta[property="og:image"]',

                // لیست بهبود یافته برای حذف محتوای ناخواسته
                'unwanted_content_selectors' => [
                    'aside', // حذف سایدبارها
                    '.ad',
                    '.inline-ad',
                    '.banner',
                    '[data-testid*="ad-"]', // حذف جامع‌تر تبلیغات
                    '[data-testid="related-content"]', // حذف مطالب مرتبط
                    '[data-testid="bottom-of-article"]', // حذف بخش پایین مقاله
                    '[data-testid="newsletter-promo"]' // حذف تبلیغ خبرنامه
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
                'title' => 'h1', // فال‌بک برای وقتی که JSON-LD کار نکند
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
                // سلکتور لینک‌ها
                'links' => 'a[href*="/news/"]',
                // فیلتر: تنها لینک‌های خبری (نه صفحات فهرست)
                'filter' => ''  // /news/2025/10/24/
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.wysiwyg--all-content',
                'cover' => '.responsive-image img',
                'cover_carousel' => 'figure img:first-child',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => [
                    '.ad',
                    '.ads-container',
                    '.banner',
                    '.sponsored-content',
                    '.share-social-media',
                    '.related-content-card',
                    '.related-articles',
                    '.article-more-stories',
                    '.recommended-stories',
                    'iframe',
                    'script',
                    'nav',
                    'footer',
                    '.comments-section',
                    'aside',
                ],
            ],
            'rate_limit' => 2,
        ],

        'Bloomberg' => [
            'category_selectors' => [
                'links' => 'a[href*="/news/articles/"]',
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => '.BasicContent_shareSmallDesktop__yhFgh',
                'cover' => '',
                'cover_carousel' => '.CarouselSlide-media:first-child picture source[media*="min-width: 1024px"][type="image/webp"], .CarouselSlide-media:first-child img.Image',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => [
                    '.BasicContent_bylineSpeech__4_VGX',
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
            'rate_limit' => 3,
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
                'cover_carousel' => 'figure.gallery .gallery-item:first-child img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.sponsor', '.inline-content'],
            ],
            'rate_limit' => 2,
        ],

        'The Wall Street Journal' => [
            'category_selectors' => [
                'links' => '.Link',
                'filter' => 'article'
            ],
            'news_selectors' => [
                'title' => 'h1.Page-headline',
                'content' => '.RichTextStoryBody',
                'cover' => '.CarouselSlide-media picture source[media*="min-width: 1024px"][type="image/webp"], .CarouselSlide-media img.Image',
                'cover_carousel' => '.CarouselSlide-media:first-child picture source[media*="min-width: 1024px"][type="image/webp"], .CarouselSlide-media:first-child img.Image',
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
            'rate_limit' => 3,
        ],

        'Financial Times' => [
            'category_selectors' => [
                // سلکتورهای متعدد برای پیدا کردن لینک‌ها
                'links' => '.js-teaser-standfirst-link',  // استراتژی 1: لینک‌های دارای /content/
                'filter' => ''
            ],
            'news_selectors' => [
                'title' => 'h1',
                'content' => 'div[data-testid="article-body"]',
                'cover' => 'figure img',
                'cover_carousel' => 'figure img:first-child',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => [
                    '.ad',
                    '.ads-container',
                    '.banner',
                    '.n-myft-ui',
                    '.share-buttons',
                    '.related-articles',
                    '.comments-section',
                    'iframe',
                    'script',
                    'nav',
                    'footer',
                    '.newsletter-signup',
                    'form',
                ],
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
                'cover_carousel' => '.article-featured-image .carousel .slide:first-child img',
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
                'cover_carousel' => '.slideshow .slide:first-child img.css-119ags5',
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
                'cover_carousel' => 'figure.carousel .carousel-item:first-child img',
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
                'cover_carousel' => '.article-featured-image .slideshow-item:first-child img',
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
                'cover_carousel' => '.gallery .item:first-child img.css-119ags5',
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
                'cover_carousel' => 'figure.slideshow .slide:first-child img',
                'cover_alt' => 'meta[property="og:image"]',
                'unwanted_content_selectors' => ['.ad', '.banner', '.sponsor'],
            ],
            'rate_limit' => 2,
        ],
    ],
];
