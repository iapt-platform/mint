import { useState } from "react";
import { Space, Button } from "antd";
import lodash from "lodash";
import { marked } from "marked";

import FooterBar from "../../components/library/FooterBar";

import HeadBar from "../../components/library/HeadBar";
import Home from "../../components/nut/Home";
import InnerDrawer from "../../components/nut/InnerDrawer";

interface IRandomPanel {
  v1: string;
  v2: string;
}

interface IMermaidProps {
  value: string;
}

const Mermaid = ({ value }: IMermaidProps) => {
  return <pre className="mermaid">{value}</pre>;
};

const Widget = () => {
  const [rdp, setRdp] = useState<IRandomPanel>({
    v1: "",
    v2: "",
  });
  var aaa: any = {};
  aaa["bbb"] = "hi";
  aaa[123] = 321;
  aaa.hi = "hello";
  console.log(aaa);

  return (
    <div>
      <HeadBar />
      <div>Home Page</div>

      <InnerDrawer />
      <div>
        <h1>Mermaid</h1>
        <div>
          <Mermaid
            value={`graph TD 
        A[Client] --> B[Load Balancer] 
        B --> C[Server01] 
        B --> D[Server02]`}
          />
        </div>
        <h1>random</h1>
        <div>
          &nbsp;
          <Space style={{ color: "blue" }}>{lodash.uniqueId("hi-")}</Space>
          &nbsp;
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
      </div>

      <div>
        <Home />
      </div>
      <FooterBar />
    </div>
  );
};

export default Widget;
