import { Space } from "antd";
import { useIntl } from "react-intl";
import logo_mps from "../../assets/general/images/logo_mps.png";

const BeiAnWidget = () => {
  const intl = useIntl();
  return (
    <>
      {import.meta.env.VITE_REACT_APP_ICP_CODE ? (
        <Space>
          <span>
            {intl.formatMessage({
              id: `labels.icp`,
            })}
            <a
              href="https://beian.miit.gov.cn/"
              target="_blank"
              rel="noreferrer"
            >
              {import.meta.env.VITE_REACT_APP_ICP_CODE}
            </a>
          </span>
          <Space>
            <img alt="code" src={logo_mps} style={{ width: 20, height: 20 }} />
            {import.meta.env.VITE_REACT_APP_MPS_CODE
              ? import.meta.env.VITE_REACT_APP_MPS_CODE
              : "滇公网安备[审批中]号"}
          </Space>
        </Space>
      ) : undefined}
    </>
  );
};

export default BeiAnWidget;
