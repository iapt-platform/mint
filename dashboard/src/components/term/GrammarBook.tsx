import { Input, List } from "antd";
import { useState } from "react";
import { ITerm, getGrammar } from "../../reducers/term-vocabulary";
import { useAppSelector } from "../../hooks";
import TermSearch from "./TermSearch";

const { Search } = Input;

const GrammarBookWidget = () => {
  const [result, setResult] = useState<ITerm[]>();
  const [termId, setTermId] = useState<string>();

  const sysGrammar = useAppSelector(getGrammar);
  return (
    <div>
      <Search
        placeholder="input search text"
        onSearch={(value: string) => {}}
        onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
          console.debug("on change", event.target.value);
          setTermId(undefined);
          const keyWord = event.target.value;
          const search = sysGrammar?.filter(
            (value) =>
              value.word.toLowerCase().includes(keyWord) ||
              value.meaning.includes(keyWord)
          );
          setResult(search);
        }}
        style={{ width: "100%" }}
      />
      <div>
        {termId ? (
          <TermSearch wordId={termId} />
        ) : (
          <List
            size="small"
            dataSource={result}
            renderItem={(item) => (
              <List.Item
                key={item.guid}
                style={{ cursor: "pointer" }}
                onClick={() => {
                  setTermId(item.guid);
                }}
              >
                <List.Item.Meta title={item.word} description={item.meaning} />
              </List.Item>
            )}
          />
        )}
      </div>
    </div>
  );
};

export default GrammarBookWidget;
