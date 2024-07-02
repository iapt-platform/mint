import { Button, Tooltip, Typography } from "antd";
import { CopyOutlined } from "@ant-design/icons";

import Mermaid from "../../general/Mermaid";
import { useAppSelector } from "../../../hooks";
import { getTerm } from "../../../reducers/term-vocabulary";
import { IWbwRelation } from "./WbwDetailRelation";
import { IWbw } from "./WbwWord";
const { Text } = Typography;

interface IWidget {
  wbwData?: IWbw[];
}
const RelaGraphicWidget = ({ wbwData }: IWidget) => {
  const terms = useAppSelector(getTerm);

  //根据relation 绘制关系图
  function sent_show_rel_map(data?: IWbw[]): string {
    let mermaid: string = "flowchart LR\n";

    const relationWords = data
      ?.filter((value) => value.relation)
      .map((item) => {
        if (item.relation && item.relation.value) {
          const json: IWbwRelation[] = JSON.parse(item.relation.value);
          const graphic = json.map((relation) => {
            const localName = terms?.find(
              (item) => item.word === relation.relation
            )?.meaning;
            const meaning = item.meaning?.value
              ? item.meaning?.value
                  ?.replaceAll("[", "")
                  .replaceAll("]", "")
                  .replaceAll("{", "")
                  .replaceAll("}", "")
              : "";
            return `${relation.sour_id}(${relation.sour_spell}<br />${meaning}) --"${relation.relation}<br />${localName}"--> ${relation.dest_id}("${relation.dest_spell}")\n`;
          });
          return graphic.join("");
        } else {
          return "";
        }
      });
    mermaid += relationWords?.join("");
    console.log("mermaid", mermaid);
    return mermaid;
  }
  const mermaidText = sent_show_rel_map(wbwData);
  return (
    <div>
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <div>
          <Text copyable={{ text: mermaidText, tooltips: "复制mermaid代码" }} />
        </div>
        <div></div>
      </div>
      <div>
        <Mermaid text={mermaidText} />
      </div>
    </div>
  );
};

export default RelaGraphicWidget;
