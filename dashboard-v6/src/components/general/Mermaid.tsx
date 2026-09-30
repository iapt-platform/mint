import lodash from "lodash";
import mermaid from "mermaid";
import { useEffect, useRef } from "react";
import { useIntl } from "react-intl";

interface IWidget {
  text?: string;
}

const MermaidWidget = ({ text }: IWidget) => {
  const intl = useIntl();
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    let cancelled = false;
    const container = containerRef.current;
    if (!container) {
      return;
    }
    container.innerHTML = "";

    const id = lodash.times(20, () => lodash.random(35).toString(36)).join("");

    // mermaid v10+ 的 render 是异步的，返回 Promise<{svg, bindFunctions}>
    (async () => {
      try {
        const { svg } = await mermaid.render(`g-${id}`, text ?? "");
        if (!cancelled) {
          container.innerHTML = svg;
        }
      } catch (error) {
        if (!cancelled) {
          console.error("[mermaid] render failed", error);
          const message =
            error instanceof Error ? error.message : String(error);
          const title = intl.formatMessage({
            id: "labels.error.render-failed",
            defaultMessage: "图形渲染失败",
          });
          container.innerHTML = "";
          const errorEl = document.createElement("div");
          errorEl.setAttribute("role", "alert");
          errorEl.style.cssText =
            "color: #cf1322; background: #fff2f0; border: 1px solid #ffccc7; border-radius: 8px; padding: 8px 12px;";
          errorEl.textContent = `${title}：${message}`;
          container.appendChild(errorEl);
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [intl, text]);

  return <div ref={containerRef} />;
};

export default MermaidWidget;
