interface IWidget {
  src?: string;
  type?: string;
}
export const Widget = ({ src, type }: IWidget) => {
  return (
    <div>
      <video controls={true} autoPlay={true}>
        <source src={src} type={type} />
        Video not playing? <a href={type}>Download file</a> instead.
      </video>
    </div>
  );
};

export default Widget;
