const FeedbackWidget = () => {
  return (
    <>
      {import.meta.env.VITE_REACT_APP_QUESTIONNAIRE_LINK ? (
        <span>
          请点击
          <a
            href={import.meta.env.VITE_REACT_APP_QUESTIONNAIRE_LINK}
            target="_blank"
            rel="noreferrer"
          >
            腾讯问卷
          </a>
          填写您的反馈
        </span>
      ) : undefined}
    </>
  );
};

export default FeedbackWidget;
