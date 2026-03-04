// ─────────────────────────────────────────────
// useArticleMutations.ts
// ─────────────────────────────────────────────
/**
 * useArticleMutations
 *
 * 文章的创建、更新、删除操作，通过 callbacks 将结果回传给调用方。
 * 不包含任何 UI 反馈（toast、跳转等），由 feature 层的 callbacks 负责。
 *
 * @returns
 *   - submitting.creating  创建请求进行中
 *   - submitting.updating  更新请求进行中
 *   - submitting.deleting  删除请求进行中
 *   - errorCode            HTTP 错误码，无错误时为 null
 *   - errorMessage         后端错误信息，无错误时为 null
 *   - createArticle        创建文章
 *   - updateArticle        更新文章
 *   - deleteArticle        删除文章
 *
 * @example
 * const { submitting, createArticle, updateArticle, deleteArticle } =
 *   useArticleMutations();
 *
 * // 创建
 * createArticle(
 *   { title: '新文章', lang: 'zh', studio: 'my-studio' },
 *   {
 *     onSuccess: (data) => {
 *       notification.success({ message: '创建成功' });
 *       navigate(`/article/${data.uid}`);
 *     },
 *     onError: (code, message) => notification.error({ message }),
 *   }
 * );
 *
 * // 更新
 * updateArticle(
 *   articleId,
 *   { ...formValues },
 *   { onSuccess: () => notification.success({ message: '保存成功' }) }
 * );
 *
 * // 删除
 * deleteArticle(id, {
 *   onSuccess: () => {
 *     notification.success({ message: '删除成功' });
 *     refresh(); // 刷新列表
 *   },
 *   onError: (code, message) => notification.error({ message }),
 * });
 *
 * <Button loading={submitting.creating}>创建</Button>
 * <Button loading={submitting.deleting}>删除</Button>
 */

import { useState, useCallback } from "react";

import {
  createArticle,
  updateArticle,
  deleteArticle,
} from "../../../api/Article";
import type {
  IArticleDataResponse,
  IArticleCreateRequest,
  IArticleDataRequest,
} from "../../../api/Article";

// ─────────────────────────────────────────────
// Callbacks
// ─────────────────────────────────────────────

interface IArticleMutationCallbacks {
  onSuccess?: (data: IArticleDataResponse) => void;
  onError?: (errorCode: number, errorMessage: string) => void;
}

interface IDeleteMutationCallbacks {
  onSuccess?: (data: number) => void;
  onError?: (errorCode: number, errorMessage: string) => void;
}

interface IMutationErrorCallbacks {
  onError?: (errorCode: number, errorMessage: string) => void;
}

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

interface ISubmitting {
  creating: boolean;
  updating: boolean;
  deleting: boolean;
}

interface IUseArticleMutationsReturn {
  submitting: ISubmitting;
  errorCode: number | null;
  errorMessage: string | null;
  createArticle: (
    data: IArticleCreateRequest,
    callbacks?: IArticleMutationCallbacks
  ) => Promise<void>;
  updateArticle: (
    id: string,
    data: IArticleDataRequest,
    callbacks?: IArticleMutationCallbacks
  ) => Promise<void>;
  deleteArticle: (
    id: string,
    callbacks?: IDeleteMutationCallbacks
  ) => Promise<void>;
}

// ─────────────────────────────────────────────
// Hook
// ─────────────────────────────────────────────

export const useArticleMutations = (): IUseArticleMutationsReturn => {
  const [submitting, setSubmitting] = useState<ISubmitting>({
    creating: false,
    updating: false,
    deleting: false,
  });
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const clearError = () => {
    setErrorCode(null);
    setErrorMessage(null);
  };

  const applyError = (e: unknown, callbacks?: IMutationErrorCallbacks) => {
    const code =
      typeof (e as Record<string, unknown>)?.status === "number"
        ? ((e as Record<string, unknown>).status as number)
        : 500;
    const message = e instanceof Error ? e.message : "Unknown error";
    setErrorCode(code);
    setErrorMessage(message);
    callbacks?.onError?.(code, message);
  };

  const handleCreate = useCallback(
    async (
      data: IArticleCreateRequest,
      callbacks?: IArticleMutationCallbacks
    ) => {
      clearError();
      setSubmitting((prev) => ({ ...prev, creating: true }));
      try {
        const res = await createArticle(data);
        if (res.ok) {
          callbacks?.onSuccess?.(res.data);
        } else {
          setErrorCode(400);
          setErrorMessage(res.message);
          callbacks?.onError?.(400, res.message);
        }
      } catch (e: unknown) {
        applyError(e, callbacks);
      } finally {
        setSubmitting((prev) => ({ ...prev, creating: false }));
      }
    },
    []
  );

  const handleUpdate = useCallback(
    async (
      id: string,
      data: IArticleDataRequest,
      callbacks?: IArticleMutationCallbacks
    ) => {
      clearError();
      setSubmitting((prev) => ({ ...prev, updating: true }));
      try {
        const res = await updateArticle(id, data);
        if (res.ok) {
          callbacks?.onSuccess?.(res.data);
        } else {
          setErrorCode(400);
          setErrorMessage(res.message);
          callbacks?.onError?.(400, res.message);
        }
      } catch (e: unknown) {
        applyError(e, callbacks);
      } finally {
        setSubmitting((prev) => ({ ...prev, updating: false }));
      }
    },
    []
  );

  const handleDelete = useCallback(
    async (id: string, callbacks?: IDeleteMutationCallbacks) => {
      clearError();
      setSubmitting((prev) => ({ ...prev, deleting: true }));
      try {
        const res = await deleteArticle(id);
        if (res.ok) {
          callbacks?.onSuccess?.(res.data);
        } else {
          setErrorCode(400);
          setErrorMessage(res.message);
          callbacks?.onError?.(400, res.message);
        }
      } catch (e: unknown) {
        applyError(e, callbacks);
      } finally {
        setSubmitting((prev) => ({ ...prev, deleting: false }));
      }
    },
    []
  );

  return {
    submitting,
    errorCode,
    errorMessage,
    createArticle: handleCreate,
    updateArticle: handleUpdate,
    deleteArticle: handleDelete,
  };
};
