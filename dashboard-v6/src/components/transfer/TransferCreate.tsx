import { ModalForm, ProForm } from "@ant-design/pro-components";
import { Alert, Form, message, notification } from "antd";
import { post } from "../../request";
import { ITransferCreateResponse, ITransferRequest } from "../api/Transfer";
import { useIntl } from "react-intl";
import UserSelect from "../template/UserSelect";
import { useEffect, useState } from "react";
import { TResType } from "../discussion/DiscussionListCard";

interface IWidget {
  studioName?: string;
  resType: TResType;
  resId?: string[];
  resName?: string;
  open?: boolean;
  onOpenChange?: Function;
  onCreate?: Function;
}
const TransferCreateWidget = ({
  studioName,
  resType,
  resId,
  resName,
  open = false,
  onOpenChange,
  onCreate,
}: IWidget) => {
  const intl = useIntl();
  const [form] = Form.useForm<{ studio: string }>();
  const [modalVisit, setModalVisit] = useState(open);
  useEffect(() => setModalVisit(open), [open]);
  const strTransfer = intl.formatMessage({
    id: `columns.studio.transfer.title`,
  });
  return (
    <ModalForm<{
      studio: string;
    }>
      open={modalVisit}
      onOpenChange={(visible) => {
        if (typeof onOpenChange !== "undefined") {
          onOpenChange(visible);
        }
      }}
      title={intl.formatMessage({
        id: `columns.studio.transfer.title`,
      })}
      form={form}
      autoFocusFirstInput
      modalProps={{
        destroyOnClose: true,
        onCancel: () => console.log("run"),
      }}
      submitTimeout={2000}
      onFinish={async (values) => {
        console.log(values);
        if (typeof resId === "undefined") {
          console.error("res id is undefined");
          return;
        }
        const data = {
          res_type: resType,
          res_id: resId,
          new_owner: values.studio,
        };
        const res = await post<ITransferRequest, ITransferCreateResponse>(
          `/v2/transfer`,
          data
        );
        if (res.ok) {
          if (typeof onCreate === "undefined") {
            notification.open({
              message: strTransfer,
              description: `${resType} ${resName} 等 ${res.data} 个资源已经转出。请等待对方确认。可以在转移管理中查看状态或取消。`,
              duration: 0,
            });
          } else {
            onCreate();
          }
        } else {
          message.error(res.message, 10);
        }
        return true;
      }}
    >
      <Alert
        message={`将${resName} ${strTransfer}下面的用户。操作后需要等待对方确认后，资源才会被转移。可以在${strTransfer}查看和取消`}
      />
      <ProForm.Group>
        <UserSelect name="studio" multiple={false} />
      </ProForm.Group>
    </ModalForm>
  );
};

export default TransferCreateWidget;
