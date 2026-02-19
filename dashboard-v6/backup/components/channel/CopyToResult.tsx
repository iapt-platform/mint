import { Button, Result } from "antd";

interface IWidget {
  total?: number;
  onClose?: Function;
  onInit?: Function;
}
const CopyToResult = ({ total, onClose, onInit }: IWidget) => {
  return (
    <Result
      status="success"
      title="Successfully Copied!"
      subTitle={`Sentence: ${total}`}
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

export default CopyToResult;
