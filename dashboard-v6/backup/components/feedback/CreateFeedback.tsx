import modal from "antd/lib/modal";
import CommentCreate from "../discussion/DiscussionCreate";

const CreateFeedbackWidget = () => {
  const path = window.location.pathname;
  console.log(path);
  return (
    <div>
      <CommentCreate
        resType="feedback"
        resId="9b4f1b1e-dfe7-41dd-8a05-ae94acb61479"
        onCreated={() => {
          modal.info({
            title: "反馈",
            content: "您的意见已经提交。感谢您的反馈。",
          });
        }}
      />
    </div>
  );
};

export default CreateFeedbackWidget;
