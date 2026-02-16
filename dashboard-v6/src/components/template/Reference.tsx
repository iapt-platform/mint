import { Typography } from "antd";
import { useIntl } from "react-intl";

const { Text, Paragraph } = Typography;

const ucFirst = (input: string) => {
  if (typeof input !== "string" || input.length === 0) {
    return input; // 如果输入不是字符串或者字符串为空，则直接返回原值
  }
  return input.charAt(0).toUpperCase() + input.slice(1);
};

interface IReference {
  sn: number;
  title: string;
  copyright: string;
}

interface IReferenceCtl {
  pali?: IReference[];
}
const ReferenceCtl = ({ pali }: IReferenceCtl) => {
  const intl = useIntl();

  const Reference = (ref: IReference) => {
    return (
      <Paragraph>{`[${ref.sn}] ${ucFirst(ref.title)} ${
        ref.copyright
      }`}</Paragraph>
    );
  };
  return (
    <>
      {pali?.map((item, id) => {
        return Reference(item);
      })}
    </>
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IReferenceCtl;
  console.log(prop);
  return (
    <>
      <ReferenceCtl {...prop} />
    </>
  );
};

export default Widget;
