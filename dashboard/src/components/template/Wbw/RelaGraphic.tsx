import Mermaid from "../../general/Mermaid";
import { useAppSelector } from "../../../hooks";
import { getTerm } from "../../../reducers/term-vocabulary";
import { IRelation } from "./WbwDetailRelation";
import { IWbw } from "./WbwWord";

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
          const json: IRelation[] = JSON.parse(item.relation.value);
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
            return `${relation.sour_id}(${relation.sour_spell}<br />${meaning}) --"${relation.relation}<br />${localName}"--> ${relation.dest_id}(${relation.dest_spell})\n`;
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

  return (
    <div>
      <Mermaid text={sent_show_rel_map(wbwData)} />
    </div>
  );
};

export default RelaGraphicWidget;
