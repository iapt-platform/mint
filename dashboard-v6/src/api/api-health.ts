// src/api/api-health.ts

import { get } from "../request";

export interface IHealth {
  createdAt: string;
}
export const apiServerHealth = (): Promise<IHealth> => {
  return get<IHealth>("/api/v2/health-check");
};
