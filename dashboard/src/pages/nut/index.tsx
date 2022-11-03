import { useState } from "react";
import { Tag, Space, Button } from "antd";
import lodash from "lodash";

import FooterBar from "../../components/library/FooterBar";

import HeadBar from "../../components/library/HeadBar";
import Home from "../../components/nut/Home";

interface IRandomPanel {
  v1: string;
  v2: string;
}

const Widget = () => {
  const [rdp, setRdp] = useState<IRandomPanel>({
    v1: "",
    v2: "",
  });
  return (
    <div>
      <HeadBar />
      <div>Home Page</div>
      <div>
        <h1>random</h1>
        <Space style={{ color: "red" }}>{rdp.v1}</Space>
        &nbsp;
        <Space style={{ color: "green" }}>{rdp.v2}</Space>
        &nbsp;
        <Button
          onClick={() => {
            setRdp({
              v1: Array.from(Array(20), () =>
                Math.floor(Math.random() * 36).toString(36)
              ).join(""),
              v2: lodash
                .times(20, () => lodash.random(35).toString(36))
                .join(""),
            });
          }}
        >
          Generate
        </Button>
      </div>
      <br />
      <div>
        <Tag
          onClick={() => {
            console.log("test tag was clicked");
          }}
        >
          Test
        </Tag>
      </div>
      <div>
        <Home />
      </div>
      <FooterBar />
    </div>
  );
};

export default Widget;
