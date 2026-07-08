export default {
  name: "Allure PHP Unit",
  output: "./build/allure-report",
  plugins: {
    testops: {
      options: {
        launchName: `Allure PHP Unit GitHub actions run (${new Date().toISOString()})`,
      },
    },
  },
};
