import type { IUser } from "./Auth";
import type { ITerm } from "./Term";

export interface IRelationRequest {
  id?: string;
  name: string;
  name_channel?: string;
  name_term?: ITerm;
  case?: string | null;
  from?: IFrom | null;
  to?: IFrom | null;
  match?: string[];
  category?: string;
  category_channel?: string;
  category_term?: ITerm;
  editor?: IUser;
  updated_at?: string;
  created_at?: string;
}
export interface IRelationListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IRelationRequest[];
    count: number;
  };
}
export interface IRelationResponse {
  ok: boolean;
  message: string;
  data: IRelationRequest;
}
export interface IFrom {
  spell?: string;
  case?: string[];
}
export interface IRelation {
  sn?: number;
  id?: string;
  name: string;
  name_channel?: string;
  name_term?: ITerm;
  case?: string | null;
  from?: IFrom | null;
  fromCase?: string[];
  fromSpell?: string;
  to?: IFrom | null;
  toCase?: string[];
  toSpell?: string;
  match?: string[];
  category?: string;
  category_channel?: string;
  category_term?: ITerm;
  editor?: IUser;
  updated_at?: string;
  created_at?: string;
}
