import { DatePicker, Space, Switch } from "antd";
import moment from "moment";
import { useState } from "react";
const PlanDate = () => {
  const [time, setTime] = useState(false);
  return (
    <DatePicker.RangePicker
      placeholder={["", "截止日期"]}
      defaultValue={[moment(), moment()]}
      showTime={time}
      bordered={false}
      renderExtraFooter={(mode) => {
        return (
          <Space>
            {"具体时间"}
            <Switch
              onChange={(checked) => {
                setTime(checked);
              }}
            />
          </Space>
        );
      }}
      allowEmpty={[true, false]}
      onChange={(date, dateString) => {
        console.log(date, dateString);
      }}
    />
  );
};

export default PlanDate;
