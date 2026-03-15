import { DatePicker, Space, Switch } from "antd";
import dayjs from "dayjs";
import { useState } from "react";
const PlanDate = () => {
  const [time, setTime] = useState(false);
  return (
    <DatePicker.RangePicker
      placeholder={["", "截止日期"]}
      defaultValue={[dayjs(), dayjs()]}
      showTime={time}
      bordered={false}
      renderExtraFooter={() => {
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
