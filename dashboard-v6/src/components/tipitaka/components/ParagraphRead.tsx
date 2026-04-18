import { useSetting } from "../../../hooks/useSetting";

import type { IWidgetSentEditInner } from "../../sentence/SentEdit";

import ParagraphReadPara from "./ParagraphReadPara";
import ParagraphReadSent from "./ParagraphReadSent";

interface IWidget {
  data?: IWidgetSentEditInner[];
}
const ParagraphRead = ({ data }: IWidget) => {
  const paragraph = useSetting("setting.layout.paragraph");

  return (
    <div>
      {paragraph === "paragraph" ? (
        <ParagraphReadPara data={data} />
      ) : (
        <ParagraphReadSent data={data} />
      )}
    </div>
  );
};

export default ParagraphRead;
