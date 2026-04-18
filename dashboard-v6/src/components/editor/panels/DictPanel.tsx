import DictComponent from "../../dict/DictComponent";

/**
 * 字典面板
 * 封装成独立组件，让 React 管理生命周期，
 * 避免内联 JSX 在 rightTabs 重建时丢失内部状态。
 */
export default function DictPanel() {
  return (
    <div className="dict_component">
      <DictComponent />
    </div>
  );
}
