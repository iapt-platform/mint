const BeiAnWidget = () => {
  const hostName = document.location.hostname;
  const ending = hostName.split(".");
  const ending2 = ending[ending.length - 1];
  return (
    <>
      {ending2 === "cc" ? (
        <a href="https://beian.miit.gov.cn/" target="_blank" rel="noreferrer">
          您的备案号
        </a>
      ) : undefined}
    </>
  );
};

export default BeiAnWidget;
