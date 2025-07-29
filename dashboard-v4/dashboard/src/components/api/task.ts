/**
 *             $table->text('description',512)->nullable();
            $table->jsonb('assignees')->index()->nullable();
            $table->jsonb('roles')->index()->nullable();
            $table->uuid('executor')->index()->nullable();
            $table->uuid('executor_relation_task')->index()->nullable();
            $table->uuid('parent')->index()->nullable();
            $table->jsonb('pre_task')->index()->nullable();
            $table->uuid('owner')->index();
            $table->uuid('editor')->index();
            $table->string('status',32)->index()->default('pending');
            $table->timestamps();
 */

import { IStudio } from "../auth/Studio";
import { IUser } from "../auth/User";
import { TPrivacy } from "./ai";

export type TTaskStatus =
  | "pending"
  | "published"
  | "running"
  | "done"
  | "restarted"
  | "requested_restart"
  | "closed"
  | "canceled"
  | "expired"
  | "queue"
  | "stop"
  | "quit"
  | "pause";
export const StatusButtons: TTaskStatus[] = [
  "pending",
  "published",
  "running",
  "done",
  "restarted",
  "requested_restart",
  "quit",
];
export type TTaskType = "instance" | "workflow" | "group";

export interface IProject {
  id: string;
  sn: number;
  title: string;
  description: string | null;
  weight: number;
}

export type TTaskCategory =
  | "translate"
  | "suggest"
  | "vocabulary"
  | "team"
  | "review"
  | "proofread";
export const ATaskCategory: TTaskCategory[] = [
  "translate",
  "suggest",
  "vocabulary",
  "team",
  "review",
  "proofread",
];
export interface ITaskData {
  id: string;
  title: string;
  description?: string | null;
  category?: TTaskCategory | null;
  progress?: number;
  html?: string | null;
  type: TTaskType;
  order?: number;
  assignees?: IUser[] | null;
  assignees_id?: string[] | null;
  parent?: ITaskData | null;
  parent_id?: string | null;
  roles?: string[] | null;
  executor?: IUser | null;
  executor_id?: string | null;
  executor_relation_task?: ITaskData | null;
  executor_relation_task_id?: string | null;
  pre_task?: ITaskData[] | null;
  pre_task_id?: string | null;
  next_task?: ITaskData[] | null;
  next_task_id?: string | null;
  is_milestone: boolean;
  project?: IProject | null;
  project_id?: string | null;
  owner?: IStudio;
  owner_id?: string | null;
  editor?: IUser;
  editor_id?: string | null;
  status?: TTaskStatus;
  created_at?: string;
  updated_at?: string;
  started_at?: string | null;
  finished_at?: string | null;
  children?: ITaskData[];
}

export interface ITaskUpdateRequest {
  id: string;
  studio_name: string;
  title?: string;
  description?: string | null;
  category?: TTaskCategory | null;
  type?: TTaskType;
  assignees_id?: string[] | null;
  parent_id?: string | null;
  project_id?: string | null;
  roles?: string[] | null;
  executor_id?: string | null;
  executor_relation_task_id?: string | null;
  pre_task_id?: string | null;
  next_task_id?: string | null;
  is_milestone?: boolean;
  status?: string;
}

export interface ITaskListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ITaskData[];
    count: number;
  };
}

export interface ITaskCreateRequest {
  title: string;
  studio: string;
  type: TTaskType;
}

export interface ITaskResponse {
  ok: boolean;
  message: string;
  data: ITaskData;
}

/**
 *            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v1mc()'));
            $table->string('title',512)->index();
            $table->boolean('is_template')->index()->default(false);
            $table->text('description')->nullable();
            $table->jsonb('executors')->index()->nullable();
            $table->uuid('parent')->index()->nullable();
            $table->jsonb('milestone')->index()->nullable();
            $table->uuid('owner')->index();
            $table->uuid('editor')->index();
            $table->jsonb('status')->index();
            $table->timestamps();
 */

export interface IProjectData {
  id: string;
  title: string;
  type: TProjectType;
  weight: number;
  description: string | null;
  parent?: IProjectData | null;
  parent_id?: string | null;
  path?: IProjectData[] | null;
  executors?: IUser[] | null;
  milestone?: IMilestoneInProject[] | null;
  owner: IStudio;
  editor: IUser;
  status: ITaskStatusInProject[];
  privacy: TPrivacy;
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
  started_at?: string | null;
  finished_at?: string | null;
  children?: IProjectData[];
}

export interface IProjectUpdateRequest {
  id?: string;
  studio_name?: string;
  title: string;
  type: TProjectType;
  privacy?: TPrivacy;
  weight?: number;
  description?: string | null;
  parent_id?: string | null;
  res_id?: string;
}

export interface IProjectListResponse {
  data: { rows: IProjectData[]; count: number };
  message: string;
  ok: boolean;
}
export interface IProjectResponse {
  data: IProjectData;
  message: string;
  ok: boolean;
}
export type TProjectType = "instance" | "workflow" | "endpoint";
export interface IProjectCreateRequest {
  title: string;
  type: TProjectType;
  studio_name: string;
}

export interface IMilestoneData {
  id: string;
  title: string;
}

export interface IMilestoneCount {
  value: number;
  total: number;
}
export interface IMilestoneInProject {
  milestone: IMilestoneData;
  projects: IMilestoneCount;
  chars: IMilestoneCount;
}

export interface ITaskStatusInProject {
  status: string;
  count: number;
  percent: number;
}

export interface ITaskGroupInsertRequest {
  data: ITaskGroupInsertData[];
}
export interface ITaskGroupInsertData {
  project_id: string;
  tasks: ITaskData[];
}

export interface ITaskGroupResponse {
  ok: boolean;
  message: string;
  data: { taskCount: number; taskRelationCount: number };
}
export interface IProjectTreeInsertRequest {
  studio_name: string;
  parent_id?: string | null;
  title?: string;
  data: IProjectUpdateRequest[];
}

export interface IProjectTreeData {
  id: string;
  resId?: string;
  isLeaf: boolean;
}
export interface IProjectTreeResponse {
  ok: boolean;
  message: string;
  data: { rows: IProjectTreeData[]; count: number };
}
