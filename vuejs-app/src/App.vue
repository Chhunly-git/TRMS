<template>
  <!-- class wrapper គឺចាំបាច់បំផុតសម្រាប់ AdminLTE -->
  <div class="wrapper">
    
    <!-- 1. Left Sidebar View -->
    <router-view name="left_sidebar"></router-view>

    <!-- 2. Navbar View -->
    <router-view name="navbar"></router-view>

    <!-- 3. Main Body Content View -->
    <div class="content-wrapper">
      <router-view></router-view>
    </div>

    <!-- 4. Footer View -->
    <router-view name="footer"></router-view>

    <!-- 5. Control Right Sidebar View -->
    <router-view name="right_sidebar"></router-view>

  </div>
</template>

<script setup></script>

<style>
/* កំណត់ទំហំឱ្យពេញអេក្រង់ */
html, body, #app, .wrapper {
  min-height: 100vh !important;
  height: auto;
}
</style>