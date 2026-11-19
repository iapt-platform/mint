// src/features/admin-credits/CreditAdjustModal.tsx
/**
 * 手工加减积分。用户用 UserSelect 按用户名 / 昵称搜索选择（从账号表里点「加积分」会预填、不可改）。
 * 每次打开生成一个 ref：网络重试或连点提交只入账一次（后端按 ref 幂等）。
 */
import { useEffect, useState } from "react";
import { Alert, Form, Input, InputNumber, Modal, message } from "antd";
import { adjustCredits } from "../../api/adminCredits";
import { fieldErrorsOf } from "../../api/error";
import { useT } from "../admin-stats/helpers";
import UserSelect from "../../components/users/UserSelect";

interface Values {
  user: string;
  delta: number;
  note: string;
}

interface Props {
  open: boolean;
  /** 预填的用户（uuid）；为空时手填 */
  user?: string;
  /** 预填时显示的名字 */
  userLabel?: string;
  onClose: () => void;
  onDone: () => void;
}

const CreditAdjustModal = ({ open, user, userLabel, onClose, onDone }: Props) => {
  const t = useT();
  const [form] = Form.useForm<Values>();
  const [saving, setSaving] = useState(false);
  const [ref, setRef] = useState(() => crypto.randomUUID());
  const delta = Form.useWatch("delta", form);
  // 每次打开换一个 ref。表单随弹窗销毁重建，初值走 initialValues——不要在 afterOpenChange 里重置，
  // 那要等打开动画结束，期间已经输入的内容会被清掉
  useEffect(() => {
    if (open) setRef(crypto.randomUUID());
  }, [open]);

  const submit = async () => {
    const values = await form.validateFields();
    setSaving(true);
    try {
      const res = await adjustCredits({ ...values, ref });
      message.success(
        res.meta?.created
          ? t("admin.credits.adjust.done", { balance: res.meta?.earned_balance ?? 0 })
          : t("admin.credits.adjust.duplicate"),
      );
      onDone();
      onClose();
    } catch (e) {
      const fields = fieldErrorsOf(e);
      if (fields) {
        form.setFields(Object.entries(fields).map(([name, errors]) => ({ name: name as keyof Values, errors })));
      }
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal
      open={open}
      title={t("admin.credits.adjust.title")}
      okText={t("admin.credits.adjust.submit")}
      confirmLoading={saving}
      onOk={submit}
      onCancel={onClose}
      destroyOnHidden
    >
      <Form form={form} layout="vertical" preserve={false} initialValues={user ? { user } : undefined}>
        {user ? (
          <Form.Item name="user" label={t("admin.credits.adjust.user")} extra={userLabel} rules={[{ required: true }]}>
            <Input disabled />
          </Form.Item>
        ) : (
          <UserSelect name="user" width="xl" />
        )}
        <Form.Item
          name="delta"
          label={t("admin.credits.adjust.delta")}
          extra={t("admin.credits.adjust.delta.tip")}
          rules={[
            { required: true },
            { validator: (_, v) => (v === 0 ? Promise.reject(new Error(t("admin.credits.adjust.delta.zero"))) : Promise.resolve()) },
          ]}
        >
          <InputNumber precision={0} min={-1000000} max={1000000} style={{ width: "100%" }} />
        </Form.Item>
        <Form.Item name="note" label={t("admin.credits.adjust.note")} rules={[{ required: true, whitespace: true, max: 255 }]}>
          <Input.TextArea rows={2} placeholder={t("admin.credits.adjust.note.placeholder")} />
        </Form.Item>
        {delta != null && delta < 0 && <Alert type="warning" showIcon message={t("admin.credits.adjust.negative")} />}
      </Form>
    </Modal>
  );
};

export default CreditAdjustModal;
