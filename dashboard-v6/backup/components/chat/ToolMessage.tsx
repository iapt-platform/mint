import { Collapse } from "antd";
import type { SessionInfo } from "../../types/chat"
import { getArgs, type WikipaliSearchResponse } from "../../types/search"
import SearchResults from "./SearchResults";

const { Panel } = Collapse;

interface IWidget {
  session?: SessionInfo;
}
const ToolMessage = ({ session }: IWidget) => {
  console.debug("ai chat render", session);
  //找到llm请求的message 可能有多个
  const toolCallMessages = session?.messages.filter((msg) => msg.tool_calls);
  return (
    <div key={"tool_calls"} className="tool-result">
      {toolCallMessages?.map((msg, index) => {
        return (
          <Collapse key={index} style={{ borderRadius: 12 }}>
            {msg.tool_calls?.map((tool) => {
              //找到对应的结果
              const search = session?.messages.find(
                (msg) => msg.tool_call_id === tool.id
              );
              if (!search?.content) {
                return <>没有结果</>;
              } else {
                const searchResult = getArgs<WikipaliSearchResponse>(
                  search.content
                );
                return (
                  search?.content && (
                    <Panel
                      header={`${tool?.function.name} ${tool?.function.arguments}`}
                      key="1"
                      extra={<>{`${searchResult.hits.total.value}个结果`}</>}
                    >
                      <div className="tool-content">
                        <SearchResults data={searchResult} />
                      </div>
                    </Panel>
                  )
                );
              }
            })}
          </Collapse>
        );
      })}
    </div>
  );
};

export default ToolMessage;
