const BeiAnWidget = () => {
  const hostName = document.location.hostname;
  const ending = hostName.split(".");
  const ending2 = ending[ending.length - 1];
  return (
    <>
      {ending2 === "cc" ? (
        <span>
          {"ICP证："}
          <a href="https://beian.miit.gov.cn/" target="_blank" rel="noreferrer">
            滇ICP备2023006988号-1
          </a>
        </span>
      ) : undefined}
    </>
  );
};

export default BeiAnWidget;
