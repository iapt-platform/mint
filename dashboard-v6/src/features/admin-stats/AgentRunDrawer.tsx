// src/features/admin-stats/AgentRunDrawer.tsx
/**
 * 一次提问的研究过程：账号、路由、耗时，回答，研究计划，给作答模型的证据，全部步骤。
 * 数据来自 GET /v3/admin/agent-runs/{run}。回答与证据都按纯文本显示（来自 LLM 与用户可写的语料，不渲染 HTML）。
 */
import { Collapse, Descriptions, Drawer, Empty, Tag, Timeline, Typography } from "antd";
import { useAgentRun } from "../../hooks/useAdminStats";
import { useT } from "./helpers";

const STEP_COLOR: Record<string, string> = { tool: "green", llm: "blue", route: "purple", plan: "purple", event: "gray" };

const pre: React.CSSProperties = {
  whiteSpace: "pre-wrap",
  wordBreak: "break-word",
  maxHeight: 320,
  overflow: "auto",
  margin: 0,
  fontSize: 12,
};

const AgentRunDrawer = ({ runId, onClose }: { runId: string | null; onClose: () => void }) => {
  const t = useT();
  const { data: run, loading } = useAgentRun(runId);

  return (
    <Drawer title={t("admin.stats.run.title")} open={!!runId} onClose={onClose} size="large" loading={loading}>
      {run ? (
        <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
          <Typography.Paragraph strong style={{ fontSize: 16, margin: 0 }}>
            {run.question}
          </Typography.Paragraph>
          <Descriptions
            size="small"
            column={2}
            bordered
            items={[
              {
                key: "account",
                label: t("admin.stats.runs.account"),
                children: run.user
                  ? run.user.nickName
                  : `${t("admin.stats.runs.guest")} · ${t("admin.stats.run.device", { device: run.device_id?.slice(0, 8) ?? "?" })} · ${run.client_ip ?? ""}`,
              },
              { key: "client", label: t("admin.stats.runs.client"), children: `${run.client ?? "?"} ${run.app_version ?? ""}` },
              {
                key: "route",
                label: t("admin.stats.runs.route"),
                children: (
                  <>
                    {t(`admin.stats.route.${run.route ?? "none"}`)}
                    {run.deep ? <Tag style={{ marginLeft: 8 }}>{t("admin.stats.runs.deep")}</Tag> : null}
                  </>
                ),
              },
              { key: "status", label: t("admin.stats.runs.status"), children: t(`admin.stats.status.${run.status}`) },
              {
                key: "latency",
                label: t("admin.stats.runs.latency"),
                children: run.latency_ms != null ? `${(run.latency_ms / 1000).toFixed(1)} ${t("admin.stats.ai.seconds")}` : "—",
              },
              { key: "tokens", label: t("admin.stats.run.tokens"), children: `${run.tokens_in ?? 0} → ${run.tokens_out ?? 0}` },
              { key: "model", label: t("admin.stats.run.model"), children: `${run.model ?? "?"} · ${run.prompt_version ?? ""}` },
              { key: "thread", label: t("admin.stats.run.thread"), children: run.thread_title ?? run.thread_id },
            ]}
          />
          {run.error ? (
            <Typography.Paragraph type="danger" style={pre}>
              {t("admin.stats.run.error")}: {run.error}
            </Typography.Paragraph>
          ) : null}
          <Collapse
            defaultActiveKey={["answer", "steps"]}
            items={[
              {
                key: "answer",
                label: t("admin.stats.run.answer"),
                children: <pre style={pre}>{run.answer ?? "—"}</pre>,
              },
              {
                key: "plan",
                label: t("admin.stats.run.plan"),
                children: <pre style={pre}>{run.plan ? JSON.stringify(run.plan, null, 2) : "—"}</pre>,
              },
              {
                key: "evidence",
                label: `${t("admin.stats.run.evidence")} · ${t("admin.stats.run.chars", { chars: run.evidence?.length ?? 0 })}`,
                children: <pre style={pre}>{run.evidence ?? "—"}</pre>,
              },
              {
                key: "steps",
                label: `${t("admin.stats.run.steps")} · ${run.steps?.length ?? 0}`,
                children: run.steps?.length ? (
                  <Timeline
                    items={run.steps.map((s) => ({
                      key: s.seq,
                      color: s.error ? "red" : (STEP_COLOR[s.kind ?? ""] ?? "gray"),
                      content: (
                        <div>
                          <Typography.Text strong>{s.kind}</Typography.Text>
                          {s.name ? <Typography.Text code>{s.name}</Typography.Text> : null}
                          {s.ms != null ? <Typography.Text type="secondary"> · {s.ms} ms</Typography.Text> : null}
                          {s.tokens_in != null ? (
                            <Typography.Text type="secondary">
                              {" "}
                              · {s.tokens_in} → {s.tokens_out ?? 0} tok
                            </Typography.Text>
                          ) : null}
                          {s.result_chars != null ? (
                            <Typography.Text type="secondary">
                              {" "}
                              · {t("admin.stats.run.chars", { chars: s.result_chars })}
                            </Typography.Text>
                          ) : null}
                          {s.args ? <pre style={{ ...pre, maxHeight: 120 }}>{JSON.stringify(s.args)}</pre> : null}
                          {s.error ? <Typography.Text type="danger">{s.error}</Typography.Text> : null}
                        </div>
                      ),
                    }))}
                  />
                ) : (
                  <Empty />
                ),
              },
            ]}
          />
        </div>
      ) : null}
    </Drawer>
  );
};

export default AgentRunDrawer;
