<template>
  <form ref="form" :action="action" :method="method" @submit.prevent="submit">
    <slot></slot>
  </form>
</template>
<script>
//The ref attribute is a special Vue.js attribute that provides a reference to the DOM element, 
//which can be used to access the element's properties and methods from the component's JavaScript code.
//In this case, the ref attribute is set to "form", which means that the form element can be accessed using 
//this.$refs.form in the component's JavaScript code. 

//The :action and :method attributes are bound to the action and method properties of the component, respectively,
// using the Vue.js syntax for binding attributes to properties.
//@submit.prevent attribute is an event listener that prevents the default form submission behavior and calls the submit method of the component when the form is submitted.

//The <slot></slot> element is a Vue.js construct that allows other components or code to inject content into the component. 
//In this case, the content that is injected into the slot will be rendered inside the form element.

import { mapActions } from "vuex"; // imports the mapActions function from the Vuex library, which is used to map Vuex actions to component methods.

export default {
  //exports the component definition as the default export of the module.
  props: {
    //The props section defines the properties that can be passed into the component
    // when it is used in other components or templates. In this case, the component
    //has three props:
    //-method: sets the HTTP method used when submitting the form. Defaults to "post".
    //-action: sets the URL where the form data will be submitted.
    //-customSubmit: a Boolean flag indicating whether to use a custom submit behavior or the default behavior.
    method: {
      default: "post",
    },
    action: {
      default: "",
    },
    customSubmit: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    //The computed section defines a computed property called isLoading, which retrieves
    //the loading state from the Vuex store. This property is used to disable form submissions
    //when a server request is already in progress.
    isLoading() {
      return this.$store.state.loading;
    },
  },
  methods: {
    //The methods section defines the methods used by the component:
    ...mapActions(["error", "si"]), //error: Vuex.mapActions(['error']).error,si: Vuex.mapActions(['si']).si,
    //...mapActions([...]): uses the mapActions function to map the error and si Vuex actions to component methods.
    collectDate() {
      //collectData(): retrieves the form data as an object, with keys and values corresponding to the form
      //field names and values.
      return _.reduce( //_.reduce() is a function provided by the Lodash library. It's a higher-order function that iterates over a collection (e.g., an array, object, or string) and applies a given function to each element, reducing the collection to a single value
        $(this.$refs.form).serializeArray(), // serialize the form data into an array of objects
        (carry, item) => {//for each item in $(this.$refs.form).serializeArray() // reduce the array into a single object
          if (carry.hasOwnProperty(item.name)) {// if the object already has a property with this name
            //hasOwnProperty is often used to avoid errors when iterating over an object's properties. 
            if (typeof carry[item.name] == "string") {// if the property is a string, convert it to an array
              carry[item.name] = [carry[item.name]];
            }

            carry[item.name].push(item.value);// add the new value to the array, push add item.value to item.name in carry array
          } else {// if the object doesn't have a property with this name yet
            carry[item.name] = item.value;// add a new property with the name and value
          }

          return carry;
        },
        {}//initial carry // start with an empty object
      );
    },
    submit() {
      //submit(): handles the form submission behavior. It emits a submit event with the form data, and optionally
      // sends the data to the server using the si Vuex action.
      if (!this.isLoading) {
        this.resetErrors();
        let data = this.collectDate();

        this.$emit("submit", data); //In Vue.js, the $emit function is used to trigger a custom event on a component instance.
        //It allows a parent component to listen for and react to events emitted by its child components.

        if (!this.customSubmit) {
          this.si({
            url: this.action,
            method: this.method,
            data: data,
          })
            .then(this.serverSuccess)
            .catch(this.serverFailed);
        }
      }
    },
    serverSuccess(result) {
      //serverSuccess(result): handles the response from the server when the form submission is successful.
      //It emits a success event with the server response.
      this.$emit("success", result);
    },
    serverFailed(result) {
      //serverFailed(result): handles the response from the server when the form submission fails.
      //It checks for error messages in the response and displays them using the error Vuex action.
      if (result.hasOwnProperty("response") && result.response.status == 422) {
        if (result.response.hasOwnProperty("data")) {
          if (result.response.data.hasOwnProperty("message"))
            this.error({
              title: "خطا",
              text: result.response.data.message,
            });

          if (result.response.data.hasOwnProperty("errors"))
            this.setErrors(result.response.data.errors);
        }
      }
    },
    setErrors(errors) {
      //setErrors(errors): sets validation errors for form fields based on the errors returned from the server.
      this.eachComponent((component) => {
        if (
          component._props &&
          component._props.hasOwnProperty("field") &&
          typeof component.setValidationErrors == "function"
        ) {
          component.setValidationErrors(errors);
        }
      });
    },
    resetErrors() {
      //resetErrors(): clears any validation errors currently set for the form fields.
      this.eachComponent((component) => {
        if (
          component._props &&
          component._props.hasOwnProperty("field") &&
          typeof component.clearValidationErrors == "function"
        ) {
          component.clearValidationErrors();
        }
      });
    },
    eachComponent(fn, children) {
      //eachComponent(fn, children): recursively iterates over all child components of the form, calling
      //the specified function for each one. This is used to find form fields and apply validation errors or clear them.
      if (children == undefined) {
        this.eachComponent(fn, this.$children);
      }

      for (let x in children) {
        fn(children[x]);

        if (children[x].$children.length > 0) {
          this.eachComponent(fn, children[x].$children);
        }
      }
    },
  },
};
</script>
