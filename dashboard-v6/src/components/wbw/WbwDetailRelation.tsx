import { Button, List, Select, Space } from "antd";
import { useEffect, useMemo, useRef, useState, type JSX } from "react";
import {
  DeleteOutlined,
  PlusOutlined,
  InfoCircleOutlined,
} from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { getRelation } from "../../reducers/relation";
import { getGrammar } from "../../reducers/term-vocabulary";

import { useIntl } from "react-intl";
import store from "../../store";
import { add, relationAddParam } from "../../reducers/relation-add";
import { grammar } from "../../reducers/command";
import { openPanel } from "../../reducers/right-panel";
import type { IWbw, IWbwField } from "../../types/wbw";

interface IOptions {
  value: string;
  label: JSX.Element;
}

export interface IWbwRelation {
  sour_id: string;
  sour_spell: string;
  dest_id: string;
  dest_spell: string;
  relation?: string;
  is_new?: boolean;
}

interface IWidget {
  data: IWbw;
  onChange?: (value: IWbwField) => void;
  onAdd?: () => void;
  onFromList?: (fromList: string[] | undefined) => void;
}

const WbwDetailRelationWidget = ({
  data,
  onChange,
  onAdd,
  onFromList,
}: IWidget) => {
  const getSourId = () => `${data.book}-${data.para}-` + data.sn.join("-");

  const intl = useIntl();

  // Use a ref for onFromList to avoid stale closures without triggering re-renders
  const onFromListRef = useRef(onFromList);
  useEffect(() => {
    onFromListRef.current = onFromList;
  }, [onFromList]);

  const [relation, setRelation] = useState<IWbwRelation[]>(() => {
    if (typeof data.relation === "undefined") return [];
    return JSON.parse(data.relation?.value ? data.relation?.value : "[]");
  });

  const [newRelationName, setNewRelationName] = useState<string>();

  const terms = useAppSelector(getGrammar);
  const relations = useAppSelector(getRelation);
  const addParam = useAppSelector(relationAddParam);

  // Sync relation from external data.relation changes (after initial render)
  const prevRelationValue = useRef(data.relation?.value);
  useEffect(() => {
    if (data.relation?.value === prevRelationValue.current) return;
    prevRelationValue.current = data.relation?.value;
    const arrRelation: IWbwRelation[] = JSON.parse(
      data.relation?.value ? data.relation?.value : "[]"
    );
    setRelation(arrRelation);
  }, [data.relation?.value]);

  // Derive grammar tags from data
  const grammarTags = useMemo(() => {
    let tags = data.case?.value
      ?.replace("v:ind", "v")
      .replace("#", "$")
      .replace(":", "$")
      .replaceAll(".", "")
      .split("$");
    if (data.grammar2?.value) {
      const g2 = data.grammar2.value.replaceAll(".", "");
      tags = tags ? [g2, ...tags] : [g2];
    }
    return tags;
  }, [data.case?.value, data.grammar2?.value]);

  // Derive filtered relations (replaces the big useEffect)
  const filteredRelations = useMemo(() => {
    if (!relations) return undefined;
    return relations.filter((value) => {
      if (!value.from) return false;
      let caseMatch = true;
      let spellMatch = true;

      if (value.from.case) {
        let matchCount = 0;
        if (grammarTags) {
          for (const iterator of value.from.case) {
            if (grammarTags.includes(iterator)) matchCount++;
          }
        }
        if (matchCount !== value.from.case.length) caseMatch = false;
      }

      if (value.from.spell && data.real.value) {
        const regexString = value.from.spell.replaceAll("*", "\\w");
        const regex = new RegExp(regexString);
        spellMatch = regex.test(data.real.value);
      }

      return caseMatch && spellMatch;
    });
  }, [grammarTags, data.real.value, relations]);

  // Derive select options from filteredRelations
  const options = useMemo<IOptions[] | undefined>(() => {
    if (!filteredRelations) return undefined;
    const relationName = new Map<string, string>();
    filteredRelations.forEach((value) => {
      relationName.set(value.name, value.name);
    });
    return Array.from(relationName.keys()).map((item) => {
      const localName = terms?.find((term) => term.word === item)?.meaning;
      return {
        value: item,
        label: (
          <Space>
            {item}
            {localName}
          </Space>
        ),
      };
    });
  }, [filteredRelations, terms]);

  // Notify parent of fromList changes
  useEffect(() => {
    if (!filteredRelations) return;
    const relationFrom: string[] = [];
    filteredRelations.forEach((value) => {
      let from: string[] = [];
      if (value.from?.spell) from.push(value.from.spell);
      if (value.from?.case) from = [...from, ...value.from.case];
      const key = from.join(".");
      if (!relationFrom.includes(key)) relationFrom.push(key);
    });
    onFromListRef.current?.(relationFrom);
  }, [filteredRelations]);

  // Handle addParam apply command
  useEffect(() => {
    if (
      addParam?.command === "apply" &&
      addParam.src_sn === data.sn.join("-") &&
      addParam.target_spell
    ) {
      const newRelation: IWbwRelation = {
        sour_id: getSourId(),
        sour_spell: data.word.value,
        dest_id: addParam.target_id ?? "",
        dest_spell: addParam.target_spell,
        relation: newRelationName,
      };
      setRelation((origin) => {
        const updated = [...origin, newRelation];
        onChange?.({ field: "relation", value: JSON.stringify(updated) });
        return updated;
      });
      setNewRelationName(undefined);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [addParam?.command]);

  const newRelationRow: IWbwRelation = {
    sour_id: getSourId(),
    sour_spell: data.word.value,
    dest_id: "",
    dest_spell: "",
    relation: undefined,
    is_new: true,
  };

  const addButton = (
    <Button
      type="dashed"
      icon={<PlusOutlined />}
      onClick={() => {
        onAdd?.();
        store.dispatch(
          add({
            book: data.book,
            para: data.para,
            src_sn: data.sn.join("-"),
            command: "add",
            relations: filteredRelations,
          })
        );
      }}
    >
      {intl.formatMessage({ id: "buttons.relate.to" })}
    </Button>
  );

  return (
    <List
      itemLayout="vertical"
      size="small"
      dataSource={[...relation, newRelationRow]}
      renderItem={(item, index) => (
        <List.Item>
          <Space>
            {!item.is_new && (
              <Button
                type="text"
                icon={<DeleteOutlined />}
                onClick={() => {
                  const arrRelation = [...relation];
                  arrRelation.splice(index, 1);
                  setRelation(arrRelation);
                  onChange?.({
                    field: "relation",
                    value: JSON.stringify(arrRelation),
                  });
                }}
              />
            )}
            <Select
              defaultValue={item.relation}
              placeholder={"请选择关系"}
              allowClear={!!item.is_new}
              style={{ width: 180 }}
              onChange={(value: string) => {
                if (item.is_new) {
                  setNewRelationName(value);
                  return;
                }
                setRelation((origin) => {
                  const updated = [...origin];
                  updated[index] = { ...updated[index], relation: value };
                  onChange?.({
                    field: "relation",
                    value: JSON.stringify(updated),
                  });
                  return updated;
                });
              }}
              options={options}
            />
            <Button
              type="link"
              icon={<InfoCircleOutlined />}
              onClick={() => {
                store.dispatch(grammar(relation[index].relation));
                store.dispatch(openPanel("grammar"));
              }}
            />
            {item.dest_spell ? item.dest_spell : addButton}
          </Space>
        </List.Item>
      )}
    />
  );
};

export default WbwDetailRelationWidget;
