import { Typography } from "antd";

import Mermaid from "../../general/Mermaid";
import { useAppSelector } from "../../../hooks";
import { getGrammar } from "../../../reducers/term-vocabulary";
import { IWbwRelation } from "./WbwDetailRelation";
import { IWbw } from "./WbwWord";
import { relationWordId } from "./WbwRelationAdd";
import { useIntl } from "react-intl";

const { Text } = Typography;

const pureMeaning = (input: string | null | undefined) => {
  return input
    ? input
        ?.replaceAll("[", "")
        .replaceAll("]", "")
        .replaceAll("{", "")
        .replaceAll("}", "")
    : "";
};

interface IWidget {
  wbwData?: IWbw[];
}
const RelaGraphicWidget = ({ wbwData }: IWidget) => {
  const terms = useAppSelector(getGrammar);
  const intl = useIntl();

  const grammarStr = (input?: string | null) => {
    if (!input) {
      return "";
    }
    const g = input.split("#");
    const mType = g[0].replaceAll(".", "");
    const type = intl.formatMessage({
      id: `dict.fields.type.${mType}.short.label`,
      defaultMessage: mType,
    });
    let strGrammar: string[] = [];
    if (g.length > 1 && g[1].length > 0) {
      strGrammar = g[1].split("$").map((item, id) => {
        const mCase = item.replaceAll(".", "");
        return intl.formatMessage({
          id: `dict.fields.type.${mCase}.short.label`,
          defaultMessage: mCase,
        });
      });
    }

    let output = type;
    if (strGrammar.length > 0) {
      output += `|${strGrammar.join("·")}`;
    }
    return output;
  };

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
            const fromMeaning = pureMeaning(item.meaning?.value);
            const fromGrammar = grammarStr(item.case?.value);
            //查找目标意思
            const toWord = data.find(
              (value: IWbw) => relationWordId(value) === relation.dest_id
            );
            const toMeaning = pureMeaning(toWord?.meaning?.value);
            const toGrammar = grammarStr(toWord?.case?.value);

            return `${relation.sour_id}("${relation.sour_spell}<br />${fromMeaning}<br />${fromGrammar}") --"${relation.relation}<br />${localName}"--> ${relation.dest_id}("${relation.dest_spell}<br />${toMeaning}<br />${toGrammar}")\n`;
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
