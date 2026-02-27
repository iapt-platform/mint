export interface IPaliBookListResponse {
  name: string;
  tag: string[];
  children?: IPaliBookListResponse[];
}
