import { Button, Space } from "antd";

import { useAppSelector } from "../../hooks";
import { add, relationAddParam } from "../../reducers/relation-add";
import store from "../../store";

import { relationWordId } from "./utils";
import type { IWbw } from "../../types/wbw";

interface IWidget {
  data: IWbw;
}
const WbwRelationAddWidget = ({ data }: IWidget) => {
  const addParam = useAppSelector(relationAddParam);

  const show = addParam?.command === "add" ? true : false;

  return (
    <div style={{ position: "absolute", marginTop: "-24px" }}>
      {show ? (
        <Space>
          <Button
            onClick={() => {
              if (typeof addParam === "undefined") {
                return;
              }
              store.dispatch(
                add({
                  book: addParam.book,
                  para: addParam.para,
                  src_sn: addParam?.src_sn,
                  target_id: relationWordId(data),
                  target_spell: data.word.value,
                  command: "apply",
                })
              );
            }}
          >
            add
          </Button>
          <Button
            onClick={() => {
              if (typeof addParam === "undefined") {
                return;
              }
              store.dispatch(
                add({
                  book: addParam.book,
                  para: addParam.para,
                  src_sn: addParam.src_sn,
                  command: "cancel",
                })
              );
            }}
          >
            cancel
          </Button>
        </Space>
      ) : undefined}
    </div>
  );
};

export default WbwRelationAddWidget;
