import { Anchor } from "antd";
import lodash from "lodash";
import { useEffect, useState } from "react";
const { Link } = Anchor;

interface IHeadingAnchor {
  label: string;
  key: string;
}
interface IWidget {
  content?: string;
}
const AnchorNavWidget = ({ content }: IWidget) => {
  const [heading, setHeading] = useState<IHeadingAnchor[]>([]);
  useEffect(() => {
    let heading = document.querySelectorAll("h1,h2,h3,h4,h5,h6");
    let headingAnchor: IHeadingAnchor[] = [];
    for (let index = 0; index < heading.length; index++) {
      const element = heading[index];
      const id = lodash
        .times(20, () => lodash.random(35).toString(36))
        .join("");
      heading[index].id = id;
      headingAnchor.push({ key: `#${id}`, label: element.innerHTML });
    }
    setHeading(headingAnchor);
  }, [content]);
  return (
    <Anchor offsetTop={50}>
      {heading.map((item, index) => {
        return <Link key={index} href={item.key} title={item.label}></Link>;
      })}
    </Anchor>
  );
};

export default AnchorNavWidget;
