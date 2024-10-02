import { Space, Tooltip, Typography } from "antd";
import { useIntl } from "react-intl";
import { FieldTimeOutlined } from "@ant-design/icons";
import { useEffect, useState } from "react";
import { BaseType } from "antd/lib/typography/Base";
const { Text } = Typography;

interface IWidgetTimeShow {
  showIcon?: boolean;
  showTooltip?: boolean;
  showLabel?: boolean;
  createdAt?: string;
  updatedAt?: string;
  title?: string;
  type?: BaseType;
}

const TimeShowWidget = ({
  showIcon = true,
  showTooltip = true,
  showLabel = true,
  createdAt,
  updatedAt,
  title,
  type,
}: IWidgetTimeShow) => {
  const intl = useIntl(); //i18n
  const [passTime, setPassTime] = useState<string>();
  const [mTime, setMTime] = useState(0);

  let mTitle: string | undefined;
  let showTime: string | undefined;
  if (updatedAt && createdAt) {
    if (updatedAt === createdAt) {
      mTitle = intl.formatMessage({
        id: "labels.created-at",
      });
      showTime = createdAt;
    } else {
      mTitle = intl.formatMessage({
        id: "labels.updated-at",
      });
      showTime = updatedAt;
    }
  } else if (createdAt) {
    mTitle = intl.formatMessage({
      id: "labels.created-at",
    });
    showTime = createdAt;
  } else if (updatedAt) {
    mTitle = intl.formatMessage({
      id: "labels.updated-at",
    });
    showTime = updatedAt;
  } else {
    mTitle = undefined;
    showTime = "";
  }
  if (typeof title !== "undefined") {
    mTitle = title;
  }

  useEffect(() => {
    if (typeof createdAt === "undefined" && typeof updatedAt === "undefined") {
      return;
    }
    let timer = setInterval(() => {
      setMTime((origin) => origin + 1);
    }, 1000 * 60);
    return () => {
      clearInterval(timer);
    };
  }, []);

  useEffect(() => {
    if (typeof showTime !== "undefined" && showTime !== "") {
      setPassTime(getPassDataTime(showTime));
    }
  }, [mTime, showTime]);

  if (typeof showTime === "undefined") {
    return <></>;
  }

  const icon = showIcon ? <FieldTimeOutlined /> : <></>;

  const tooltip: string = getFullDataTime(showTime);
  const color = "lime";
  function getPassDataTime(t: string): string {
    let currDate = new Date();
    const time = new Date(t);
    let pass = currDate.getTime() - time.getTime();
    let strPassTime = "";
    if (pass < 100 * 1000) {
      //一分钟内
      strPassTime = intl.formatMessage({ id: "utilities.time.now" });
    } else if (pass < 3600 * 1000) {
      //二小时内
      strPassTime =
        Math.floor(pass / 1000 / 60) +
        intl.formatMessage({ id: "utilities.time.mins_ago" });
    } else if (pass < 3600 * 24 * 1000) {
      //二天内
      strPassTime =
        Math.floor(pass / 1000 / 3600) +
        intl.formatMessage({ id: "utilities.time.hs_ago" });
    } else if (pass < 3600 * 24 * 14 * 1000) {
      //二周内
      strPassTime =
        Math.floor(pass / 1000 / 3600 / 24) +
        intl.formatMessage({ id: "utilities.time.days_ago" });
    } else if (pass < 3600 * 24 * 30 * 1000) {
      //二个月内
      strPassTime =
        Math.floor(pass / 1000 / 3600 / 24 / 7) +
        intl.formatMessage({ id: "utilities.time.weeks_ago" });
    } else if (pass < 3600 * 24 * 365 * 1000) {
      //一年内
      strPassTime =
        Math.floor(pass / 1000 / 3600 / 24 / 30) +
        intl.formatMessage({ id: "utilities.time.months_ago" });
    } else if (pass < 3600 * 24 * 730 * 1000) {
      //超过1年小于2年
      strPassTime =
        Math.floor(pass / 1000 / 3600 / 24 / 365) +
        intl.formatMessage({ id: "utilities.time.year_ago" });
    } else {
      strPassTime =
        Math.floor(pass / 1000 / 3600 / 24 / 365) +
        intl.formatMessage({ id: "utilities.time.years_ago" });
    }
    return strPassTime;
  }
  function getFullDataTime(t: string) {
    let inputDate = new Date(t);
    return inputDate.toLocaleString();
  }

  return (
    <Tooltip title={tooltip} color={color} key={color}>
      <Text type={type}>
        <Space>
          {icon}
          {showLabel ? mTitle : ""}
          {passTime}
        </Space>
      </Text>
    </Tooltip>
  );
};

export default TimeShowWidget;
