import { Bubble, Sender } from "@ant-design/x";

const messages = [
  {
    content: "Hello, Ant Design X!",
    role: "user",
    key: "user_0",
  },
];

const App = () => (
  <div
    style={{
      height: "400px",
      display: "flex",
      flexDirection: "column",
      justifyContent: "space-between",
    }}
  >
    <Bubble.List items={messages} />
    <Sender />
  </div>
);

export default App;
