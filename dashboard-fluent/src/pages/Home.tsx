import { PrimaryButton } from "@fluentui/react";

const Widget = () => {
  return (
    <div>
      <h1>home</h1>
      <div>
        <PrimaryButton
          onClick={() => {
            alert("aaa");
          }}
        >
          Demo
        </PrimaryButton>
      </div>
    </div>
  );
};

export default Widget;
