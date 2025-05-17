export const VALID_CATEGORIES = [
  'world', 'business', 'economy', 'tech', 'science', 'personal-finance',
  'companies', 'work-careers', 'real-estate', 'lifestyle', 'arts',
  'health', 'sports', 'opinion',
  // Arts subcategories
  'arts/books', 'arts/film', 'arts/fine-art', 'arts/history', 'arts/music',
  'arts/television', 'arts/theater',
  // Personal finance subcategories
  'personal-finance/retirement', 'personal-finance/savings', 'personal-finance/credit',
  'personal-finance/taxes', 'personal-finance/mortgages',
  // Real estate subcategories
  'real-estate/commercial', 'real-estate/luxury-homes',
  // Work careers subcategories
  'work-careers/business-school-rankings', 'work-careers/business-education',
  'work-careers/europe-startup-hubs', 'work-careers/entrepreneurship',
  'work-careers/recruitment', 'work-careers/business-books',
  'work-careers/business-travel', 'work-careers/working-it'
] as const;

export type ValidCategory = typeof VALID_CATEGORIES[number];
