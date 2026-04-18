import { TermCtl, type IWidgetTermCtl } from "../term/TermCtl";

interface IWidgetTerm {
  props: string;
}
const Widget = ({ props }: IWidgetTerm) => {
  const prop = JSON.parse(atob(props)) as IWidgetTermCtl;
  return (
    <>
      <TermCtl {...prop} />
    </>
  );
};

export default Widget;
