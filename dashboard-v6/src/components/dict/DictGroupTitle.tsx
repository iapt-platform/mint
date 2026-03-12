import { Affix, Breadcrumb } from "antd";
import { useState } from "react";

interface IWidget {
  title: React.ReactNode;
  path: string[];
}

const DictGroupTitle = ({ title, path }: IWidget) => {
  const [fixed, setFixed] = useState<boolean>();
  return (
    <Affix
      offsetTop={0}
      target={() =>
        document.getElementsByClassName("dict_component")[0] as HTMLElement
      }
      onChange={(affixed) => setFixed(affixed)}
    >
      {!fixed && title}
      {fixed && (
        <Breadcrumb
          style={{
            backgroundColor: "white",
            padding: 4,
            borderBottom: "1px solid gray",
          }}
          items={[{ title: "Top" }, ...path.map((item) => ({ title: item }))]}
        />
      )}
    </Affix>
  );
};

export default DictGroupTitle;
