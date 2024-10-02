import { Button, Space } from "antd";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../../hooks";
import { add, relationAddParam } from "../../../reducers/relation-add";
import store from "../../../store";
import { IWbw } from "./WbwWord";

export const relationWordId = (word: IWbw) => {
  return `${word.book}-${word.para}-` + word.sn.join("-");
};

interface IWidget {
  data: IWbw;
}
const WbwRelationAddWidget = ({ data }: IWidget) => {
  const [show, setShow] = useState(false);
  const addParam = useAppSelector(relationAddParam);
  useEffect(() => {
    if (addParam?.command === "add") {
      setShow(true);
    } else {
      setShow(false);
    }
  }, [addParam?.command]);

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
