interface IItem {
  id: string;
  value: string;
}

const FontBoxWidget = () => {
  const items: IItem[] = [
    { id: "bbho", value: "ᨻᩮ᩠ᨽᩣ" },
    { id: "ccho", value: "ᨧᩮ᩠ᨨᩣ" },
    { id: "ddho", value: "ᨴᩮ᩠ᨵᩣ" },
    { id: "ḍḍho", value: "ᨯᩮ᩠ᨰᩣ" },
    { id: "ggho", value: "ᨣᩮ᩠ᨥᩣ" },
    { id: "jjho", value: "ᨩᩮ᩠ᨫᩣ" },
    { id: "kkho", value: "ᨠᩮ᩠ᨡᩣ" },
    { id: "mbho", value: "ᨾᩮ᩠ᨽᩣ" },
    { id: "mpho", value: "ᨾᩮ᩠ᨹᩣ" },
  ];
  return (
    <ul>
      {items.map((x) => (
        <li
          style={{
            fontFamily: "ATaiThamKHNewV3-Normal",
          }}
          key={x.id}
        >
          {x.id} {x.value}
        </li>
      ))}
    </ul>
  );
};

export default FontBoxWidget;
