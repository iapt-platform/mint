import { Col, Row } from "antd";

import type { IAnchorData } from "./DictList";
import type { IWidgetWordCardData } from "./WordCard";
import type { ICaseListData } from "./CaseList";

import WordCard from "./WordCard";
import CaseList from "./CaseList";
import DictList from "./DictList";

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

interface IWidgetDictContent {
  data: IWidgetDictContentData;
}

const Widget = (prop: IWidgetDictContent) => {
  return (
    <>
      <Row>
        <Col flex="200px">
          <DictList data={prop.data.dictlist} />
        </Col>
        <Col flex="760px">
          {prop.data.words.map((it, id) => {
            return <WordCard key={id} data={it} />;
          })}
        </Col>
        <Col flex="200px">
          <CaseList data={prop.data.caselist} />
        </Col>
      </Row>
    </>
  );
};

export default Widget;
