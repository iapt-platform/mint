// src/features/admin-credits/CreditAdmin.tsx
/**
 * 管理后台 · AI 对话积分：概览与结算任务、账号一览、全站流水，右上角手工加减积分。
 * 数据来自 /v3/admin/credit-*（仅 root / administrator）。加减或结算之后 version + 1，各块一起重取。
 */
import { useState } from "react";
import { Button, Card, Space, Tabs, Typography } from "antd";
import { PlusOutlined } from "@ant-design/icons";
import type { CreditAccount } from "../../api/adminCredits";
import { useCreditOverview } from "../../hooks/useAdminCredits";
import { useT } from "../admin-stats/helpers";
import CreditAccountTable from "./CreditAccountTable";
import CreditAdjustModal from "./CreditAdjustModal";
import CreditLedgerTable from "./CreditLedgerTable";
import CreditOverview from "./CreditOverview";

const CreditAdmin = () => {
  const t = useT();
  const [version, setVersion] = useState(0);
  const [adjusting, setAdjusting] = useState<{ open: boolean; account?: CreditAccount }>({ open: false });
  const overview = useCreditOverview(version);
  const bump = () => setVersion((v) => v + 1);

  return (
    <Space orientation="vertical" size="middle" style={{ width: "100%" }}>
      <Space style={{ width: "100%", justifyContent: "space-between" }} wrap>
        <Typography.Title level={4} style={{ margin: 0 }}>
          {t("admin.menu.credits")}
        </Typography.Title>
        <Button type="primary" icon={<PlusOutlined />} onClick={() => setAdjusting({ open: true })}>
          {t("admin.credits.adjust.title")}
        </Button>
      </Space>

      <CreditOverview data={overview.data} loading={overview.loading} onSettled={bump} />

      <Card size="small">
        <Tabs
          items={[
            {
              key: "accounts",
              label: t("admin.credits.tab.accounts"),
              children: <CreditAccountTable version={version} onAdjust={(account) => setAdjusting({ open: true, account })} />,
            },
            { key: "ledger", label: t("admin.credits.tab.ledger"), children: <CreditLedgerTable version={version} /> },
          ]}
        />
      </Card>

      <CreditAdjustModal
        open={adjusting.open}
        user={adjusting.account?.account_id}
        userLabel={adjusting.account?.user ? `${adjusting.account.user.nickName} @${adjusting.account.user.userName}` : undefined}
        onClose={() => setAdjusting({ open: false })}
        onDone={bump}
      />
    </Space>
  );
};

export default CreditAdmin;
