import { Button, Result } from "antd";

interface IWidget {
  onClose?: Function;
  onInit?: Function;
}
const CopytoResultWidget = ({ onClose, onInit }: IWidget) => {
  return (
    <Result
      status="success"
      title="Successfully Copied!"
      subTitle="Sentence: 23 , Words: 143"
      extra={[
        <Button
          key="init"
          onClick={() => {
            if (typeof onInit !== "undefined") {
              onInit();
            }
          }}
        >
          从新开始
        </Button>,
        <Button
          key="close"
          type="primary"
          onClick={() => {
            if (typeof onClose !== "undefined") {
              onClose();
            }
          }}
        >
          关闭
        </Button>,
      ]}
    />
  );
};

export default CopytoResultWidget;
