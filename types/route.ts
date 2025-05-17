export type RouteParams<T> = {
  params: Promise<T>;
};

export type NewsParams = {
  slug: string;
};
