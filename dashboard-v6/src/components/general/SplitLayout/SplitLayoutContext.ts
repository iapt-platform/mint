import { createContext, useContext } from "react";
import type { ReactNode } from "react";

export interface SplitLayoutContextValue {
  collapsed: boolean;
  toggle: () => void;
  expandButton: ReactNode;
}

export const SplitLayoutContext = createContext<SplitLayoutContextValue | null>(
  null
);

export function useSplitLayout(): SplitLayoutContextValue {
  const ctx = useContext(SplitLayoutContext);
  if (!ctx) {
    throw new Error("useSplitLayout must be used within <SplitLayout>");
  }
  return ctx;
}
