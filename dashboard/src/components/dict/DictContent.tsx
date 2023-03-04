import { Col, Row, Tabs } from "antd";

import type { IAnchorData } from "./DictList";
import type { IWidgetWordCardData } from "./WordCard";
import type { ICaseListData } from "./CaseList";

import WordCard from "./WordCard";
import CaseList from "./CaseList";
import DictList from "./DictList";
import MyCreate from "./MyCreate";

export interface IWidgetDictContentData {
  dictlist: IAnchorData[];
  words: IWidgetWordCardData[];
  caselist: ICaseListData[];
}
export interface IApiDictContentData {
  ok: boolean;
  message: string;
  data: IWidgetDictContentData;
}

interface IWidget {
  word?: string;
  data: IWidgetDictContentData;
  compact?: boolean;
}

const Widget = ({ word, data, compact }: IWidget) => {
  return (
    <>
      <Row>
        <Col flex="200px">
          {compact ? <></> : <DictList data={data.dictlist} />}
        </Col>
        <Col flex="760px">
          <Tabs
            size="small"
            items={[
              {
                label: `结果`,
                key: "result",
                children: (
                  <div>
                    {data.words.map((it, id) => {
                      return <WordCard key={id} data={it} />;
                    })}
                  </div>
                ),
              },
              {
                label: `单词本`,
                key: "my",
                children: (
                  <div>
                    <MyCreate word={word} />
                  </div>
                ),
              },
            ]}
          />
        </Col>
        <Col flex="200px">
          <CaseList word={word} />
        </Col>
      </Row>
    </>
  );
};

export default Widget;
