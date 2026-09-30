import { Alert } from "antd";
import lodash from "lodash";
import mermaid from "mermaid";
import { useEffect, useMemo, useRef, useState } from "react";
import { useIntl } from "react-intl";

interface IWidget {
  text?: string;
}

type RenderState =
  | { status: "loading" }
  | { status: "ready"; svg: string }
  | { status: "error"; message: string };

const MermaidWidget = ({ text }: IWidget) => {
  const intl = useIntl();
  const id = useMemo(
    () => lodash.times(20, () => lodash.random(35).toString(36)).join(""),
    []
  );
  const containerRef = useRef<HTMLDivElement>(null);
  const bindFunctionsRef = useRef<((element: Element) => void) | undefined>(
    undefined
  );
  const [state, setState] = useState<RenderState>({ status: "loading" });

  useEffect(() => {
    let cancelled = false;
    bindFunctionsRef.current = undefined;

    // mermaid v10+ 的 render 是异步的，返回 Promise<{svg, bindFunctions}>
    mermaid
      .render(`g-${id}`, text ?? "")
      .then(({ svg, bindFunctions }) => {
        if (cancelled) {
          return;
        }
        bindFunctionsRef.current = bindFunctions;
        setState({ status: "ready", svg });
      })
      .catch((error: unknown) => {
        if (cancelled) {
          return;
        }
        const message = error instanceof Error ? error.message : String(error);
        console.error("[mermaid] render failed", error);
        setState({ status: "error", message });
      });

    return () => {
      cancelled = true;
    };
  }, [id, text]);

  // svg 插入 DOM 之后再绑定交互回调（点击、链接等）
  useEffect(() => {
    if (
      state.status === "ready" &&
      bindFunctionsRef.current &&
      containerRef.current
    ) {
      bindFunctionsRef.current(containerRef.current);
    }
  }, [state]);

  if (state.status === "error") {
    return (
      <Alert
        type="error"
        showIcon
        title={intl.formatMessage({
          id: "labels.error.render-failed",
          defaultMessage: "图形渲染失败",
        })}
        description={state.message}
      />
    );
  }

  if (state.status === "ready") {
    return (
      <div
        ref={containerRef}
        dangerouslySetInnerHTML={{ __html: state.svg }}
      />
    );
  }

  return null;
};

export default MermaidWidget;
