import { Button, Popover, Typography } from "antd";
import type { IFilter } from "./TaskList";
import { useRef, useState } from "react";
import { useIntl } from "react-intl";

import {
  ProForm,
  type ProFormInstance,
  ProFormSelect,
} from "@ant-design/pro-components";
import { DeleteOutlined, FilterOutlined } from "@ant-design/icons";
import UserSelect from "../users/UserSelect";

const { Text } = Typography;

interface IProps {
  item: IFilter;
  sn: number;
  onRemove?: () => void;
}
const FilterItem = ({ item, sn, onRemove }: IProps) => {
  const intl = useIntl();
  const [operator, setOperator] = useState<string | null>(item.operator);
  return (
    <ProForm.Group>
      <ProFormSelect
        initialValue={item.field}
        name={`field_${sn}`}
        style={{ width: 120 }}
        options={[
          {
            value: "executor_id",
            label: intl.formatMessage({ id: "forms.fields.executor.label" }),
          },
          {
            value: "assignees_id",
            label: intl.formatMessage({ id: "forms.fields.assignees.label" }),
          },
          {
            value: "participants_id",
            label: intl.formatMessage({ id: "labels.participants" }),
          },
          {
            value: "prev_executors_id",
            label: intl.formatMessage({ id: "labels.task.prev.executors" }),
          },
        ]}
      />
      <ProFormSelect
        initialValue={item.operator}
        fieldProps={{
          value: operator,
          onChange(value) {
            setOperator(value);
          },
        }}
        name={`operator_${sn}`}
        style={{ width: 120 }}
        options={[
          "includes",
          "not-includes",
          "equal",
          "not-equal",
          "null",
          "not-null",
        ].map((item) => {
          return {
            value: item,
            label: intl.formatMessage({ id: `labels.filters.${item}` }),
          };
        })}
      />
      <UserSelect
        name={"value_" + sn}
        multiple={true}
        initialValue={item.value}
        required={false}
        hiddenTitle
        hidden={operator === "null" || operator === "not-null"}
      />
      <Button type="link" icon={<DeleteOutlined />} danger onClick={onRemove} />
    </ProForm.Group>
  );
};

interface IWidget {
  initValue?: IFilter[];
  onChange?: (value: IFilter[]) => void;
}
const Filter = ({ initValue, onChange }: IWidget) => {
  const [filterList, setFilterList] = useState(initValue ?? []);
  const intl = useIntl();
  const formRef = useRef<ProFormInstance | undefined>(undefined);
  return (
    <Popover
      placement="bottomLeft"
      trigger={"click"}
      arrow={{ pointAtCenter: true }}
      title={intl.formatMessage({ id: "labels.filter" })}
      content={
        <div style={{ width: 780 }}>
          <ProForm
            formRef={formRef}
            submitter={{
              render(_props, dom) {
                return [
                  <Button
                    onClick={() => {
                      setFilterList((origin) => {
                        return [
                          ...origin,
                          {
                            field: "executor_id",
                            operator: "includes",
                            value: [],
                          },
                        ];
                      });
                    }}
                  >
                    添加条件
                  </Button>,
                  ...dom,
                ];
              },
            }}
            onFinish={async () => {
              const value = formRef.current?.getFieldsValue();
              console.log(value);
              let counter = 0;
              const newValue: IFilter[] = [];
              while (counter < Object.keys(value).length) {
                const field = `field_${counter}`;
                const field2 = `operator_${counter}`;
                const field3 = `value_${counter}`;
                if (Object.hasOwn(value, field)) {
                  newValue.push({
                    field: value[field],
                    operator: value[field2],
                    value: value[field3],
                  });
                }
                counter++;
              }
              console.log(newValue);
              if (onChange) {
                onChange(newValue);
              }
            }}
          >
            <ProForm.Group>
              <Text type="secondary">{"满足以下"}</Text>
              <ProFormSelect
                name={"operator"}
                initialValue={"and"}
                style={{ width: 120 }}
                options={["and", "or"].map((item) => {
                  return {
                    value: item,
                    label: intl.formatMessage({ id: `labels.filters.${item}` }),
                  };
                })}
              />
            </ProForm.Group>
            <div
              style={{
                border: "1px solid rgba(128, 128, 128, 0.5)",
                borderRadius: 6,
                marginBottom: 8,
                padding: "8px 0 8px 8px",
              }}
            >
              {filterList.map((item, id) => {
                return (
                  <FilterItem
                    item={item}
                    key={id}
                    sn={id}
                    onRemove={() => {
                      setFilterList((origin) => {
                        return origin.filter(
                          (_value, index: number) => index !== id
                        );
                      });
                    }}
                  />
                );
              })}
            </div>
          </ProForm>
        </div>
      }
    >
      <Button
        type={filterList.length === 0 ? "text" : "primary"}
        icon={<FilterOutlined />}
      >
        筛选 {filterList.length}
      </Button>
    </Popover>
  );
};

export default Filter;
