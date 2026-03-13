export const api_url = (path: string) =>
  `${import.meta.env.VITE_API_BASE}${path}`;

/**API 返回结构 */
interface ApiResponse<T> {
  ok: boolean;
  message?: string;
  data: T;
}
/**分页数据结构 */
interface PaginatedData<T> {
  items: T[];
  pagination: Pagination;
}
/**分页信息 */
interface Pagination {
  page: number; // 当前页
  pageSize: number; // 每页数量
  total: number; // 总记录数
}
/** 使用 type ArticleListResponse = PaginatedResponse<Article>; */
export type PaginatedResponse<T> = ApiResponse<PaginatedData<T>>;
