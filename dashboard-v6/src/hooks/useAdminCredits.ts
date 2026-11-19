// src/hooks/useAdminCredits.ts
/**
 * 管理后台积分页的数据 hook。参数变了或调了 refresh() 就重新请求，后到的旧结果丢弃
 * （同 useAdminStats，共用它的 useAsync）。
 */
import {
  getCreditAccounts,
  getCreditLedger,
  getCreditOverview,
  type CreditAccountList,
  type CreditAccountQuery,
  type CreditLedgerList,
  type CreditLedgerQuery,
  type CreditOverview,
} from "../api/adminCredits";
import { useAsync, type AsyncState } from "./useAdminStats";

/** version 变了就重取（加减积分、结算之后由页面递增）。 */
export const useCreditOverview = (version: number): AsyncState<CreditOverview> =>
  useAsync(() => getCreditOverview(), String(version));

export const useCreditAccounts = (query: CreditAccountQuery, version: number): AsyncState<CreditAccountList> =>
  useAsync(() => getCreditAccounts(query), JSON.stringify(query) + version);

/** query 为 null 时不请求（抽屉关着）。 */
export const useCreditLedger = (query: CreditLedgerQuery | null, version: number): AsyncState<CreditLedgerList> =>
  useAsync(query ? () => getCreditLedger(query) : null, query ? JSON.stringify(query) + version : "");
