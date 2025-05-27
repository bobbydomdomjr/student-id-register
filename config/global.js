const appUrl = () => {
    const location = window.location;
    const pathArray = location.pathname.split('/');
    const projectName = pathArray[1];

    // console.log(`App URL: ${location.protocol}//${location.host}/${projectName}`);
    return `${location.protocol}//${location.host}/${projectName}`;
};

const url = appUrl();
console.log(url);
