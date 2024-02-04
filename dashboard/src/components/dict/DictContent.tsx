import { Button, Col, Divider, Row, Tabs } from "antd";

import type { IAnchorData } from "./DictList";
import type { IWidgetWordCardData } from "./WordCard";
import type { ICaseListData } from "./CaseList";

import WordCard from "./WordCard";
import CaseList from "./CaseList";
import DictList from "./DictList";
import MyCreate from "./MyCreate";
import { useIntl } from "react-intl";
import DictGroupTitle from "./DictGroupTitle";

export interface IDictWords {
  pass: string;
  words: IWidgetWordCardData[];
}

export interface IDictContentData {
  dictlist: IAnchorData[];
  words: IDictWords[];
  caselist: ICaseListData[];
  time?: number;
  count?: number;
}
export interface IApiDictContentData {
  ok: boolean;
  message: string;
  data: IDictContentData;
}

interface IWidget {
  word?: string;
  data: IDictContentData;
  compact?: boolean;
}

const DictContentWidget = ({ word, data, compact }: IWidget) => {
  const intl = useIntl();
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
                label: `查询结果`,
                key: "result",
                children: (
                  <div>
                    <div>
                      {intl.formatMessage(
                        {
                          id: "message.result",
                        },
                        { count: data.count }
                      )}
                      {" ("}
                      {intl.formatMessage(
                        {
                          id: "message.time",
                        },
                        { time: data.time?.toFixed(3) }
                      )}
                      {")"}
                    </div>
                    <div>
                      {data.words.map((it, id) => {
                        return (
                          <div>
                            <DictGroupTitle
                              title={
                                <Button style={{ width: 120 }}>
                                  {intl.formatMessage({
                                    id: `labels.dict.pass.${it.pass}`,
                                  })}
                                </Button>
                              }
                              path={[
                                intl.formatMessage({
                                  id: `labels.dict.pass.${it.pass}`,
                                }),
                              ]}
                            />
                            <div>
                              {it.words.map((word, index) => (
                                <WordCard key={index} data={word} />
                              ))}
                            </div>
                          </div>
                        );
                      })}
                    </div>
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

export default DictContentWidget;
