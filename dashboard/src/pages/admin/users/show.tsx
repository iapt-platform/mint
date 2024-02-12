import { useState } from "react";
import { useParams } from "react-router-dom";
import { Card, Tabs } from "antd";

import GoBack from "../../../components/studio/GoBack";
import Account from "../../../components/auth/Account";
import { IUserApiData } from "../../../components/api/Auth";

const Widget = () => {
  const { id } = useParams(); //url 参数
  const [title, setTitle] = useState("");

  return (
    <Card title={<GoBack to={`/admin/users/list`} title={title} />}>
      <Tabs
        size="small"
        items={[
          {
            label: `基本`,
            key: "basic",
            children: (
              <Account
                userId={id}
                onLoad={(value: IUserApiData) => setTitle(value.nickName)}
              />
            ),
          },
          {
            label: `统计`,
            key: "term",
            children: <></>,
          },
        ]}
      />
    </Card>
  );
};

export default Widget;
