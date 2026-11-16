import { useState } from "react";
import { useIntl } from "react-intl";
import { useParams } from "react-router";
import { Card, Tabs } from "antd";
import type { IUserApiData } from "../../../api/Auth";
import Account from "../../../components/auth/Account";
import GoBack from "../../../components/studio/GoBack";

/** 用户详情：基本信息（统计页签留空，与 v4 一致）。 */
const Widget = () => {
  const intl = useIntl();
  const { id } = useParams();
  const [title, setTitle] = useState("");

  return (
    <>
      <title>{title || intl.formatMessage({ id: "admin.menu.users" })}</title>
      <Card title={<GoBack to="/admin/users" title={title} />}>
        <Tabs
          size="small"
          items={[
            {
              label: intl.formatMessage({ id: "admin.users.basic" }),
              key: "basic",
              children: <Account userId={id} onLoad={(value: IUserApiData) => setTitle(value.nickName)} />,
            },
            { label: intl.formatMessage({ id: "admin.users.statistics" }), key: "statistics", children: <></> },
          ]}
        />
      </Card>
    </>
  );
};

export default Widget;
