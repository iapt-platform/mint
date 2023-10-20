import { useIntl } from "react-intl";

const BeiAnWidget = () => {
  const intl = useIntl();
  return (
    <>
      {process.env.REACT_APP_ICP_CODE ? (
        <span>
          {intl.formatMessage({
            id: `labels.icp`,
          })}
          <a href="https://beian.miit.gov.cn/" target="_blank" rel="noreferrer">
            {process.env.REACT_APP_ICP_CODE}
          </a>
        </span>
      ) : undefined}
    </>
  );
};

export default BeiAnWidget;
