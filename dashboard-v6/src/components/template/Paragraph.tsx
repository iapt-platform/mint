import type { IParagraphProps } from "../tipitaka/components/Paragraph";
import Paragraph from "../tipitaka/components/Paragraph";

interface IWidget {
  props: string;
  children?: React.ReactNode | React.ReactNode[];
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IParagraphProps;
  return (
    <>
      <Paragraph {...prop} />
    </>
  );
};

export default Widget;
