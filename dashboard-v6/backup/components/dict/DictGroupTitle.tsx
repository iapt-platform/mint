import { Affix, Breadcrumb } from "antd";
import { useState } from "react";

interface IWidget {
  title: React.ReactNode;
  path: string[];
}

const DictGroupTitleWidget = ({ title, path }: IWidget) => {
  const [fixed, setFixed] = useState<boolean>();
  return (
    <Affix
      offsetTop={0}
      target={() =>
        document.getElementsByClassName("dict_component")[0] as HTMLElement
      }
      onChange={(affixed) => setFixed(affixed)}
    >
      {fixed ? (
        <Breadcrumb
          style={{
            backgroundColor: "white",
            padding: 4,
            borderBottom: "1px solid gray",
          }}
        >
          <Breadcrumb.Item key={"top"}>Top</Breadcrumb.Item>
          {path.map((item, index) => {
            return <Breadcrumb.Item key={index}>{item}</Breadcrumb.Item>;
          })}
        </Breadcrumb>
      ) : (
        title
      )}
    </Affix>
  );
};

export default DictGroupTitleWidget;
