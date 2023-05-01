import { Button, List, Select, Space } from "antd";
import { useEffect, useState } from "react";
import { DeleteOutlined, PlusOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../../hooks";
import { getRelation } from "../../../reducers/relation";
import { getTerm } from "../../../reducers/term-vocabulary";
import { IWbw } from "./WbwWord";
import { useIntl } from "react-intl";
import store from "../../../store";
import { add, relationAddParam } from "../../../reducers/relation-add";

interface IOptions {
  value: string;
  label: JSX.Element;
}

interface IRelation {
  sour_id: string;
  sour_spell: string;
  dest_id: string;
  dest_spell: string;
  relation?: string;
}
interface IWidget {
  data: IWbw;
  onChange?: Function;
  onAdd?: Function;
}
const WbwDetailRelationWidget = ({ data, onChange, onAdd }: IWidget) => {
  const intl = useIntl();
  const [relation, setRelation] = useState<IRelation[]>([]);
  const [options, setOptions] = useState<IOptions[]>();
  const terms = useAppSelector(getTerm);
  const relations = useAppSelector(getRelation);

  const addParam = useAppSelector(relationAddParam);
  useEffect(() => {
    if (
      addParam?.command === "apply" &&
      addParam.src_sn === data.sn.join("-") &&
      addParam.target_spell
    ) {
      const newRelation: IRelation = {
        sour_id: `${data.book}-${data.para}-` + data.sn.join("-"),
        sour_spell: data.word.value,
        dest_id: `${addParam.book}-${addParam.para}-` + addParam.target_id,
        dest_spell: addParam.target_spell,
      };
      setRelation([...relation, newRelation]);
    }
  }, [addParam?.command]);

  useEffect(() => {
    if (typeof data.relation === "undefined") {
      return;
    }
    const arrRelation: IRelation[] = JSON.parse(data.relation?.value);
    setRelation(arrRelation);
  }, [data.relation]);

  useEffect(() => {
    const caseEnd = data.case?.value.split("$");
    if (typeof caseEnd === "undefined") {
      return;
    }
    const mRelation = relations
      ?.filter(
        (value) =>
          value.case === caseEnd[caseEnd.length - 1].replaceAll(".", "")
      )
      .map((item) => {
        const localName = terms?.find(
          (term) => term.word === item.name
        )?.meaning;
        return {
          value: item.name,
          label: (
            <Space>
              {item.name}
              {localName}
            </Space>
          ),
        };
      });
    setOptions(mRelation);
  }, [data.case?.value, relations, terms]);
  return (
    <List
      itemLayout="vertical"
      size="small"
      header={
        <Button
          type="dashed"
          icon={<PlusOutlined />}
          onClick={() => {
            if (typeof onAdd !== "undefined") {
              onAdd();
            }
            store.dispatch(
              add({
                book: data.book,
                para: data.para,
                src_sn: data.sn.join("-"),
                command: "add",
              })
            );
          }}
        >
          {intl.formatMessage({ id: "buttons.add" })}
        </Button>
      }
      dataSource={relation}
      renderItem={(item, index) => (
        <List.Item>
          <Space>
            <Button
              type="text"
              icon={<DeleteOutlined />}
              onClick={() => {
                let arrRelation: IRelation[] = [...relation];
                arrRelation.splice(index, 1);
                setRelation(arrRelation);
                if (typeof onChange !== "undefined") {
                  onChange({
                    field: "relation",
                    value: JSON.stringify(arrRelation),
                  });
                }
              }}
            />
            {item.dest_spell}
            <Select
              defaultValue={item.relation}
              style={{ width: 180 }}
              onChange={(value: string) => {
                console.log(`selected ${value}`);
                let arrRelation: IRelation[] = [...relation];
                arrRelation[index].relation = value;
                setRelation(arrRelation);
                if (typeof onChange !== "undefined") {
                  onChange({
                    field: "relation",
                    value: JSON.stringify(arrRelation),
                  });
                }
              }}
              options={options}
            />
          </Space>
        </List.Item>
      )}
    />
  );
};

export default WbwDetailRelationWidget;
