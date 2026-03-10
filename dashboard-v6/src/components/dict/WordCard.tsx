import { Flex, Space, Typography } from "antd";

import GrammarPop from "./GrammarPop";
import WordCardByDict from "./WordCardByDict";
import { useIntl } from "react-intl";
import Community from "./Community";
import TermCommunity from "../term/TermCommunity";
import type { IWordCardData } from "../../api/dict";

const { Title, Text } = Typography;

interface IWidgetWordCard {
  data: IWordCardData;
}
const WordCardWidget = ({ data }: IWidgetWordCard) => {
  const intl = useIntl();
  const caseList = data.case?.map((element) => {
    return element.split("|").map((it, id) => {
      if (it.slice(0, 1) === "@") {
        const [showText, keyText] = it.slice(1).split("-");
        return <GrammarPop key={id} gid={keyText} text={showText} />;
      } else {
        return <span key={id * 200}>{it}</span>;
      }
    });
  });
  return (
    <Flex gap="medium" vertical>
      <Title level={4} id={data.anchor}>
        {data.word}
      </Title>
      {data.grammar.length > 0 ? (
        <WordCardByDict
          data={{
            dictname: "语法信息",
            description: "列出可能的语法信息供参考",
            anchor: "anchor",
          }}
        >
          <div>
            <Text>
              {data.grammar.length > 0 ? data.grammar[0].factors : ""}
            </Text>
          </div>
          <div>
            <Text>{data.parents}</Text>
          </div>
          <div>
            {data.grammar
              .filter((item) => item.confidence > 0.5)
              .map((it, id) => {
                const grammar = it.grammar.split("$");
                const grammarGuide = grammar.map((item, id) => {
                  const strCase = item.replaceAll(".", "");
                  return (
                    <GrammarPop
                      key={id}
                      gid={strCase}
                      text={intl.formatMessage({
                        id: `dict.fields.type.${strCase}.label`,
                        defaultMessage: strCase,
                      })}
                    />
                  );
                });
                return (
                  <div key={id}>
                    <Space>{grammarGuide}</Space>
                  </div>
                );
              })}
          </div>
          <div>
            <Text>{caseList}</Text>
          </div>
        </WordCardByDict>
      ) : (
        <></>
      )}
      <Community word={data.word} />
      <TermCommunity word={data.word} />
      {data.dict.map((it, id) => {
        return <WordCardByDict key={id} data={it} />;
      })}
    </Flex>
  );
};

export default WordCardWidget;
